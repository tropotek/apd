<?php
namespace App\Console;

use App\Db\PathCaseMap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CompleteCases extends \Bs\Console\Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('complete-cases')
            ->setAliases(['cc'])
            ->setDescription("Complete cases older than a month that have been invoiced with no out-standing requests.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        // required vars
        $config = \App\Config::getInstance();

        // Mark all path cases that have been invoiced and all requests processed and are older than 1 month as completed
        $sql = <<<SQL
WITH
completed AS (
    SELECT
        r.path_case_id,
        COUNT(*) as cnt,
        MAX(s.created) AS last_completed
    FROM status s
    JOIN request r on (s.fid = r.id)
    WHERE s.fkey = 'App\\\Db\\\Request'
    AND NOT s.del
    AND s.name = 'completed'
    GROUP BY r.path_case_id
),
requests AS (
    SELECT
        r.path_case_id,
        p.pathologist_id,
        p.account_status,
        COUNT(*) AS total,
        SUM(IF(r.status = 'pending', 1, 0)) AS pending_cnt,
        SUM(IF(r.status = 'completed', 1, 0)) AS complete_cnt,
        c.last_completed,
        p.status AS 'case_status',
        p.created AS 'case_created'
    FROM request r
    LEFT JOIN path_case p ON (r.path_case_id = p.id)
    LEFT JOIN completed c ON (p.id = c.path_case_id)
    WHERE
        r.status != 'cancelled'
        AND p.pathologist_id > 0
        -- all cases that would need reminders (not needed if we auto set cases to completed with the below condition)
--        AND (p.account_status = '' OR p.account_status = 'pending')
        -- find cases to be set to status 'completed'
--        AND (p.account_status = 'invoiced' OR p.account_status = 'uvetInvoiced')
    GROUP BY r.path_case_id
)
SELECT *
FROM requests r
WHERE r.case_status NOT IN ('completed','cancelled', 'reported')
--   AND r.pending_cnt = 0
   AND (r.account_status = 'invoiced' OR r.account_status = 'uvetInvoiced')
   AND last_completed < NOW() - INTERVAL 1 MONTH
SQL;
        $rows = $config->getDb()->query($sql);

vd($rows->rowCount());

        foreach ($rows as $row) {
            vd($row);
            /** @var \App\Db\PathCase $pathCase */
            $pathCase = PathCaseMap::create()->find($row->path_case_id);
            $pathCase->setStatusNotify(false);
            $pathCase->setStatus(\App\Db\PathCase::STATUS_COMPLETED);
            $pathCase->save();
        }


        $output->writeln('Complete!!!');
        return 0;
    }



}
