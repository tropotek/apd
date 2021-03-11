<?php
namespace App\Ajax;

use App\Config;
use App\Db\NoticeMap;
use App\Db\PathCaseMap;
use Uni\Db\User;
use Uni\Db\UserMap;
use Exception;
use Tk\ConfigTrait;
use Tk\Db\Tool;
use Tk\Request;
use Tk\Response;
use Tk\ResponseJson;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Mce
{
    use ConfigTrait;


    /**
     * params:
     *   [
     *     'obj' => 'ObjectClass',
     *     'id' => 1
     *     'fieldName' => 'formField',
     *     'value' => 'field value to be updated to'
     *   ]
     *
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMceAutosave(Request $request)
    {
        //$id = 0;
        // From post params
        $obj = $request->get('obj');
        $id = $request->get('id');
        $fieldName = $request->get('fieldName');
        $value = $request->get('value');
        if ($id == 0) {
            vd();
            return ResponseJson::createJson(['ok' => 'Cannot save a new case field.']);
        }

        //vd($request->all());
        if ($fieldName && $obj == 'PathCase') {
            if (!$this->getAuthUser() || !$this->getAuthUser()->isStaff())
                return ResponseJson::createJson(['err' => 'No valid user found.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            $case = PathCaseMap::create()->find($id);
            if ($case) {
                $method = 'set' . ucfirst($fieldName);
                if (method_exists($case, $method)) {
                    $case->$method($value);
                    $case->save();
                    return ResponseJson::createJson(['ok' => 'Case field saved.']);
                }
            }
        }

        return ResponseJson::createJson(['err' => 'Field not updated, check params.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}