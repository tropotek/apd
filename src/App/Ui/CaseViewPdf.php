<?php
namespace App\Ui;

use App\Db\PathCase;
use App\Db\RequestMap;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Db\Tool;
use Uni\Uri;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 *
 * @note This file uses the mpdf lib
 * @link https://mpdf.github.io/
 */
class CaseViewPdf extends Pdf
{
    /**
     * @var PathCase
     */
    protected $pathCase = null;

    /**
     * @param PathCase $pathCase
     * @throws \Exception
     */
    public function __construct(PathCase $pathCase)
    {
        $this->pathCase = $pathCase;
        $watermark = '';
        parent::__construct('',
            $this->pathCase->getPathologyId() . ' - ' . ucwords($this->pathCase->getType()),
            $watermark
        );

        $this->mpdf->SetHTMLHeaderByName('myHeader1');
        $this->mpdf->SetHTMLFooterByName('myFooter1');

    }

    /**
     * @param PathCase $pathCase
     * @return Pdf
     * @throws \Exception
     */
    public static function createReport(PathCase $pathCase)
    {
        $obj = new static($pathCase);
        return $obj;
    }

    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return null|Template|Renderer
     * @throws \Exception
     */
    public function show()
    {
        $template = $this->getTemplate();
        $template->setTitleText($this->getTitle());
        if ($this->rendered) return $template;
        $this->rendered = true;


        $institution = $this->pathCase->getInstitution();
        if ($institution->getLogoUrl()) {
            $template->setAttr('logo', 'src', $institution->getLogoUrl());
            $template->setVisible('logo');
        }

        $template->appendText('pageTitle', $institution->getName() . ' - ' . ucfirst($this->pathCase->getType()));
        //$template->appendText('pageTitle', 'Veterinary Anatomic Pathology');

        $template->appendText('headerTitle', 'ID: ' . $this->pathCase->getPathologyId());


        /*
        $inst = sprintf('<p>%s</p><blockquote>%s<br/>%s</blockquote><p>%s</p><p>%s</p>',
            $institution->getName(),
            $institution->getStreet(),
            $institution->getCity() . ' ' . $institution->getState() . ' ' . $institution->getPostcode(),
            'Phone: ' . $institution->getPhone(),
            'Email: ' . $institution->getEmail()
        );
        $template->appendHtml('institution', $inst);
        */


        $template->appendText('pathologyId', $this->pathCase->getPathologyId());
        $template->appendText('arrival', $this->pathCase->getArrival(\Tk\Date::FORMAT_SHORT_DATE));
        $template->appendText('submissionType', $this->pathCase->getSubmissionType());
        if ($this->pathCase->getClient())
            $template->appendText('clientId', $this->pathCase->getClient()->getDisplayName());
        $template->appendText('created', $this->pathCase->getCreated(\Tk\Date::FORMAT_SHORT_DATE));
        $template->appendText('billable', ($this->pathCase->isBillable() ? 'Yes' : 'No'));
        $template->appendText('accountStatus', $this->pathCase->getAccountStatus());
        $template->appendText('submissionReceived', ($this->pathCase->isSubmissionReceived() ? 'Yes' : 'No'));
        $template->appendText('afterHours', ($this->pathCase->isAfterHours() ? 'Yes' : 'No'));
        $template->appendText('bioSamples', $this->pathCase->getBioSamples());
        $template->appendText('bioNotes', $this->pathCase->getBioNotes());
        $template->appendText('status', $this->pathCase->getStatus());
        $template->appendText('ownerName', $this->pathCase->getOwnerName());
        $template->appendText('animalName', $this->pathCase->getAnimalName());
        $template->appendText('patientNumber', $this->pathCase->getPatientNumber());
        $template->appendText('microchip', $this->pathCase->getMicrochip());
        if ($this->pathCase->getAnimalType())
            $template->appendText('animalType', $this->pathCase->getAnimalType()->getName());

        $template->appendText('species', $this->pathCase->getSpecies());
        $template->appendText('specimenCount', $this->pathCase->getSpecimenCount());

        $sex = 'Male';
        if (strtoupper($this->pathCase->getSex()) != 'M')
            $sex = 'Female';

        $desexed = '';
        if ($this->pathCase->isDesexed())
            $desexed = ' (Desexed)';

        $template->appendText('sex', $sex . $desexed);

        $template->appendText('colour', $this->pathCase->getColour());
        $template->appendText('weight', $this->pathCase->getWeight());
        $template->appendText('age', sprintf('%sy %sm', $this->pathCase->getAge(), $this->pathCase->getAgeMonths()));

        if ($this->pathCase->getDob())
            $template->appendText('dob', $this->pathCase->getDob(\Tk\Date::FORMAT_SHORT_DATE));
        if ($this->pathCase->getDod())
            $template->appendText('dod', $this->pathCase->getDod(\Tk\Date::FORMAT_SHORT_DATE));
        if ($this->pathCase->getPathologist())
            $template->appendText('pathologistId', $this->pathCase->getPathologist()->getName());
        $students = $this->pathCase->getStudentList();
        if (count($students)) {
            $list = '';
            foreach ($students as $student) {
                $list .= sprintf('<small>%s</small><br/>', $student->getName());
            }
            $list .= '';
            $template->appendHtml('students', $list);
        }

        $template->appendText('euthanised', ($this->pathCase->isEuthanised() ? 'Yes' : 'No'));
        $template->appendText('euthanisedMethod', $this->pathCase->getEuthanisedMethod());
        $template->appendText('zootonic', $this->pathCase->getZoonotic());
        $template->appendText('issue', $this->pathCase->getIssue());


        if ($this->pathCase->getClinicalHistory()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Clinical History:');
            $block->appendHtml('content', $this->pathCase->getClinicalHistory());
            $block->appendRepeat();
        }




        if ($this->pathCase->getGrossPathology()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Gross Pathology:');
            $block->appendHtml('content', $this->pathCase->getGrossPathology());
            $block->appendRepeat();
        }
//        if ($this->pathCase->getGrossMorphologicalDiagnosis()) {
//            $block = $template->getRepeat('textBlock');
//            $block->appendText('title', 'Gross Morphological Diagnosis:');
//            $block->appendHtml('content', $this->pathCase->getGrossMorphologicalDiagnosis());
//            $block->appendRepeat();
//        }
        if ($this->pathCase->getHistopathology()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Histopathology:');
            $block->appendHtml('content', $this->pathCase->getHistopathology());
            $block->appendRepeat();
        }
        if ($this->pathCase->getAncillaryTesting()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Ancillary Testing:');
            $block->appendHtml('content', $this->pathCase->getAncillaryTesting());
            $block->appendRepeat();
        }
        if ($this->pathCase->getMorphologicalDiagnosis()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Morphological Diagnosis:');
            $block->appendHtml('content', $this->pathCase->getMorphologicalDiagnosis());
            $block->appendRepeat();
        }
        if ($this->pathCase->getCauseOfDeath()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Cause Of Death:');
            $block->appendHtml('content', $this->pathCase->getCauseOfDeath());
            $block->appendRepeat();
        }
        if ($this->pathCase->getComments()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Comments:');
            $block->appendHtml('content', $this->pathCase->getComments());
            $block->appendRepeat();
        }
        if ($this->pathCase->getSecondOpinion()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Second Opinion:');
            $block->appendHtml('content', $this->pathCase->getSecondOpinion());
            $block->appendRepeat();
        }
        if ($this->pathCase->getAddendum()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Addendum:');
            $block->appendHtml('content', $this->pathCase->getAddendum());
            $block->appendRepeat();
        }

//        $pathologist = $this->pathCase->getPathologist();
//        if ($pathologist) {
//            $template->appendText('pathologistName', $pathologist->getName());
//            if ($pathologist->getCredentials())
//                $template->appendText('pathologistCreds', ' ('.$pathologist->getCredentials().')');
//            if ($pathologist->getPosition())
//                $template->appendText('pathologistPosition', $pathologist->getPosition());
//
//            $template->setVisible('pathologist');
//        }


        $requestList = RequestMap::create()->findFiltered([
            'pathCaseId' => $this->pathCase->getId(),
            'status' => [\App\Db\Request::STATUS_PENDING, \App\Db\Request::STATUS_COMPLETED]
        ], Tool::create('created DESC'));

        if (count($requestList)) {
            foreach ($requestList as $request) {
                $row = $template->getRepeat('row');
                $row->insertText('status', ucfirst($request->getStatus()));
                $row->insertText('qty', $request->getQty());
                if ($request->getService())
                    $row->insertText('service', $request->getService()->getName());
                if ($request->getCassette())
                    $row->insertText('cassette', $request->getCassette()->getNumber());
                if ($request->getTest())
                    $row->insertText('test', $request->getTest()->getName());
                $row->appendRepeat();
            }
            $template->setVisible('requests');
        }



        $css = <<<CSS
@page {
 header: myHeader1;
 footer: myFooter1;
}
p {
    padding: 0 0;
    margin: 0.5em 0 0 0;
    line-height: 1.3em;
}
h1, h2, h3, h4, h5, h6 {
  margin: 0.3em 0 0 0;
}
table.head-t {
  padding: 0;
}
table.head-t td {
  padding: 0 7px;
  margin: 0;
  border: none;
  font-style: italic;
  font-size: 0.9em;
  color: #666;
}
body {
  font-size: 0.8em;
}
.content {
  margin-left: 10px;
}
table td {
  vertical-align: top;
  line-height: 1.3em;
}
table.border {
  border: 1px solid #CCC;
}
table.details td {
  padding: 4px 10px;
  width: 25%;
}
table.details td.label {
  font-weight: bold;
  text-align: right;
}
table.requests td {
  text-align: center;
  padding: 4px;
}
table.requests th {
  padding: 4px;
  border-bottom: 1px solid #CCC;
  background-color: #EEEEEE;
}
.textBlock {
  margin-top: 10px;
}
CSS;
        $template->appendCss($css);

        $js = <<<JS
window.addEventListener('load', function() {
    var list = document.getElementsByClassName("html-hide");
    for(var i = list.length - 1; 0 <= i; i--)
      if(list[i] && list[i].parentElement)
        list[i].parentElement.removeChild(list[i]);
})
JS;
        $template->appendJs($js);


        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title></title>
</head>
<body class="" style="">

  <htmlpageheader class="html-hide" id="myHeader1" name="myHeader1">
    <table width="100%" class="head-t">
      <tr>
        <td width="50%">Case Report</td>
        <td width="50%" style="text-align: right;" var="headerTitle"></td>
      </tr>
    </table>
  </htmlpageheader>
  
  <htmlpagefooter class="html-hide" id="myFooter1" name="myFooter1">
    <table width="100%" class="head-t">
      <tr>
        <td width="50%">Date: {DATE j-m-Y}</td>
        <td width="50%" style="text-align: right;">{PAGENO}/{nbpg}</td>
      </tr>
    </table>
  </htmlpagefooter>


  <div class="content">
    <h2 style="text-align: center;" var="pageTitle"></h2>
    <p>&nbsp;</p>
    
    <div class="header">
      <table width="100%" style="margin: 0 auto;" class="border details">
        <tr>
          <td class="label">Pathology ID:</td>
          <td><span var="pathologyId"></span></td>
          <td class="label">Arrival:</td>
          <td><span var="arrival"></span></td>
        </tr>
        <tr>
          <td class="label">Submission Type:</td>
          <td><span var="submissionType"></span></td>
          <td class="label">Submitting Client:</td>
          <td><span var="clientId"></span></td>
        </tr>
        <tr>
          <td class="label">Submission Date:</td>
          <td><span var="created"></span></td>
          <td class="label"></td>
          <td></td>
        </tr>
        <tr>
          <td class="label">Billable:</td>
          <td><span var="billable"></span></td>
          <td class="label">Account Status:</td>
          <td><span var="accountStatus"></span></td>
        </tr>
        <tr>
          <td class="label">Submission Received:</td>
          <td><span var="submissionReceived"></span></td>
          <td class="label">After Hours:</td>
          <td><span var="afterHours"></span></td>
        </tr>
        <tr>
          <td class="label">Bio Samples:</td>
          <td><span var="bioSamples"></span></td>
          <td class="label">Bio Notes:</td>
          <td><span var="bioNotes"></span></td>
        </tr>
        <tr>
          <td class="label">Status:</td>
          <td><span var="status"></span></td>
          <td class="label">Owner Name:</td>
          <td><span var="ownerName"></span></td>
        </tr>
        <tr>
          <td class="label">Animal Name/ID:</td>
          <td><span var="animalName"></span></td>
          <td class="label">Patient Number:</td>
          <td><span var="patientNumber"></span></td>
        </tr>
        <tr>
          <td class="label">Microchip:</td>
          <td><span var="microchip"></span></td>
          <td class="label">Animal Type:</td>
          <td><span var="animalType"></span></td>
        </tr>
        <tr>
          <td class="label">Species/Breed:</td>
          <td><span var="species"></span></td>
          <td class="label">Animal Count:</td>
          <td><span var="specimenCount"></span></td>
        </tr>
        <tr>
          <td class="label">Sex:</td>
          <td><span var="sex"></span></td>
          <td class="label">Weight:</td>
          <td><span var="weight"></span></td>
        </tr>
        <tr>
          <td class="label">Colour:</td>
          <td><span var="colour"></span></td>
          <td class="label">Age:</td>
          <td><span var="age"></span></td>
        </tr>
        <tr>
          <td class="label">DOB:</td>
          <td><span var="dob"></span></td>
          <td class="label">DOD:</td>
          <td><span var="dod"></span></td>
        </tr>
        <tr>
          <td class="label">Pathologist:</td>
          <td><span var="pathologistId"></span></td>
          <td class="label">Students:</td>
          <td><span var="students"></span></td>
        </tr>
        <tr>
          <td class="label">Euthanised:</td>
          <td><span var="euthanised"></span></td>
          <td class="label">Euthanised Method:</td>
          <td><span var="euthanisedMethod"></span></td>
        </tr>
        <tr>
          <td class="label">Zoonotic/Other Risks:</td>
          <td><span var="zootnic"></span></td>
          <td class="label">Case Issues:</td>
          <td><span var="issue"></span></td>
        </tr>
      </table>
      
      
      <div class="textBlock" style="" repeat="textBlock" var="textBlock">
        <h4 var="title"></h4>
        <div var="content"></div>
      </div>
      
    </div>
    
    <div class="requestList" style="page-break-before: always;" choice="requests">
      <h2 style="text-align: center;">Request List</h2>
      <p>&nbsp;</p>
      <table width="100%" style="margin: 0 auto;" class="border requests" cellpadding="0" cellspacing="0">
        <tr>
          <th>Status</th>
          <th>Qty</th>
          <th>Service</th>
          <th>Cassette</th>
          <th>Test</th>
        </tr>
        <tr repeat="row">
          <td var="status"></td>
          <td var="qty"></td>
          <td var="service"></td>
          <td var="cassette"></td>
          <td var="test"></td>
        </tr>
      </table>
      
    </div>
    
    
  </div>
</body>
</html>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}