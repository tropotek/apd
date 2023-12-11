<?php
namespace App\Ui;

use App\Db\CompanyContactMap;
use App\Db\CompanyMap;
use App\Db\PathCase;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\ConfigTrait;
use Tk\Db\Tool;
use Tk\Request;


class CompanyInfo extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use ConfigTrait;

    /**
     * @var null|PathCase
     */
    protected $pathCase = null;

    protected $canEdit = false;


    /**
     * @param PathCase $pathCase
     */
    public function __construct($pathCase, bool $canEdit = false)
    {
        $this->pathCase = $pathCase;
        $this->canEdit = $canEdit;
    }

    public function doDefault(Request $request)
    {
        if ($request->has('cid')) {
            $this->doGetCompanyInfo($request);
        }
    }

    public function doGetCompanyInfo(Request $request)
    {
        $data = [];
        $companyId = $request->request->getInt('cid');
        $contacts = $request->request->get('cn');
        $company = CompanyMap::create()->find($companyId);
        $data['company'] = null;
        $data['contacts'] = null;
        if ($company) {
            $data['company'] = (array)$company;
            $data['company']['address'] = $company->getAddress();
            if ($contacts && is_array($contacts)) {
                $data['contacts'] = CompanyContactMap::create()->findFiltered([
                    'id' => $contacts,
                    'companyId' => $company->getId(),
                ], Tool::create('name'))->toArray();
            }
        }

        \Tk\ResponseJson::createJson($data)->send();
        exit();
    }

    /**
     * @return Renderer|Template|void|null
     */
    public function show()
    {
        $template = $this->getTemplate();
        $canEdit = json_encode($this->canEdit);

        $js = <<<JS
jQuery(function ($) {
    const canEdit = {$canEdit};
    const panel = $('.company-info-panel .company-info');
    const companySel = $('[name=companyId]');
    const contactSel = $("[name='contacts[]']");
    
    $(document).on('company-panel.refresh', function() {
        
        $.get(document.location, {
            cid : companySel.val(),
            cn : contactSel.val(),
            crumb_ignore: 'crumb_ignore',
            nolog: 'nolog'
        })
        .done(function (data) {
            var html = '';

            panel.empty();
            if (data.company) {
                var company = data.company;
                var companyName = company.name;
                if (canEdit) {
                    companyName = '<a href="companyEdit.html?companyId=' + company.id + '" target="_blank">' + company.name + '</a>';
                }
                html = '<p><strong title="Client Name" class="name">' + companyName + '</strong></p>';
                html += '<ul>';
                if (company.email)
                    html += '<li><i class="fa fa-envelope-o"></i> <a href="mailto:' + company.email + '">' + company.email + '</a></li>';
                if (company.phone)
                    html += '<li><i class="fa fa-phone"></i> <a href="tel:' + company.phone + '">' + company.phone + '</a></li>';
                if (company.fax)
                    html += '<li><i class="fa fa-fax"></i> <a href="tel:' + company.fax + '">' + company.fax + '</a></li>';
                if (company.address)
                    html += '<li><i class="fa fa-building"></i> <a href="tel:' + company.address + '">' + company.address + '</a></li>';
                html += '</ul>';
                
                if (data.contacts && data.contacts.length) {
                    var contacts = data.contacts ?? [];
                    html += '<p>Client Contacts:</p>';
                    html += '<ul>';
                    for (const contact of contacts) {
                        var details = [];
                        if (contact.name) {
                            var name = '<span>' + contact.name + '</span>';
                            if (canEdit) {
                                name = '<a href="companyContactEdit.html?companyContactId='+contact.id+'" target="_blank">' + contact.name + '</a>';
                            }
                            details.push(name);
                        }
                        if (contact.email) {
                            details.push('<a href="mailto:'+contact.email+'">' + contact.email + '</email>');
                        }
                        if (contact.phone) {
                            details.push('<a href="tel:'+contact.phone.replace(/[^0-9]/g, '')+'">' + contact.phone + '</a>');
                        }
                        
                        html += '<li style="font-size: 0.9em;">' + details.join(' - ') + '</li>';
                    }
                    html += '</ul>';
                }
                panel.append(html);
                panel.closest('.tk-panel').show();
            } else {
                panel.closest('.tk-panel').hide();
            }
        });
    }).trigger('company-panel.refresh');
    
});
JS;
        $template->appendJs($js);

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel company-info-panel" data-panel-title="Client Details" data-panel-icon="fa fa-building-o" var="panel">
    <div class="company-info"></div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}