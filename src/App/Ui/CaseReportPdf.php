<?php
namespace App\Ui;

use App\Db\PathCase;
use Dom\Renderer\Renderer;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 *
 * @note This file uses the mpdf lib
 * @link https://mpdf.github.io/
 */
class CaseReportPdf extends Pdf
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
        if($pathCase->getReportStatus() === PathCase::REPORT_STATUS_INTERIM) {
            $watermark = 'INTERIM';
        }
        if ($pathCase->isStudentReport()) {
            $watermark = 'Student Report';
        }
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

        $template->appendText('pageTitle', $institution->getName());
        //$template->appendText('pageTitle', 'Veterinary Anatomic Pathology');

        $template->appendText('headerTitle', 'ID: ' . $this->pathCase->getPathologyId());

        $inst = sprintf('%s<br/>%s<br/>%s<br/>%s<br/>%s<br/>%s',
            $institution->getName(),
            $institution->getStreet(),
            $institution->getCity() . ' ' . $institution->getState() . ' ' . $institution->getPostcode(),
            'Phone: ' . $institution->getPhone(),
            'Fax: ' . $institution->getData()->get(\App\Controller\Institution\Edit::INSTITUTION_FAX),
            'Email: ' . $institution->getEmail()
        );
        $template->appendHtml('institution', $inst);

        $template->appendText('submissionDate', $this->pathCase->getCreated(\Tk\Date::FORMAT_SHORT_DATE));
        $template->appendText('pathologyId', $this->pathCase->getPathologyId());
        $template->appendText('name', ucwords($this->pathCase->getReportStatus()) . ' Report');

        if ($this->pathCase->getClient())
            $template->appendText('clientName', $this->pathCase->getClient()->getNameFirst());

        $owner = $this->pathCase->getOwner();
        if ($owner) {
            $template->appendText('ownerName', $owner->getNameFirst());
            $template->appendText('ownerPhone', $owner->getPhone());
            $template->appendText('ownerAddress', $owner->getStreet());
            $template->appendText('ownerFax', $owner->getFax());
            $template->appendText('ownerCity', $owner->getCity());
            $template->appendText('ownerEmail', $owner->getEmail());
        }

        $template->appendText('animalName', $this->pathCase->getAnimalName());
        $template->appendText('patientNumber', $this->pathCase->getPatientNumber());
        $template->appendText('species', $this->pathCase->getSpecies());
        $template->appendText('breed', $this->pathCase->getBreed());
        $template->appendText('age', sprintf('%sy %sm', $this->pathCase->getAge(), $this->pathCase->getAgeMonths()));
        $sex = 'Male';
        if (strtoupper($this->pathCase->getSex()) != 'M')
            $sex = 'Female';

        $spayed = '';
        if ($this->pathCase->isDesexed())
            $spayed = ' Spayed';

        $template->appendText('sex', $sex . $spayed);


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
//        if ($this->pathCase->getGrossMorphologicalDiagnosis()) {
//            $block = $template->getRepeat('textBlock');
//            $block->appendText('title', 'Gross Morphological Diagnosis:');
//            $block->appendHtml('content', $this->pathCase->getGrossMorphologicalDiagnosis());
//            $block->appendRepeat();
//        }
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
        if ($this->pathCase->getAddendum()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Addendum:');
            $block->appendHtml('content', $this->pathCase->getAddendum());
            $block->appendRepeat();
        }

        $pathologist = $this->pathCase->getPathologist();
        if ($pathologist) {
            $template->appendText('pathologistName', $pathologist->getName());
            if ($pathologist->getCredentials())
                $template->appendText('pathologistCreds', ' ('.$pathologist->getCredentials().')');
            if ($pathologist->getPosition())
                $template->appendText('pathologistPosition', $pathologist->getPosition());

            $template->setVisible('pathologist');
        }
        //$template->appendText('date', \Tk\Date::create()->format(\Tk\Date::FORMAT_SHORT_DATE));


        // TODO: Re-Add this once we have the new file upload field ready
//        $allFiles = $this->pathCase->getFiles();
//        if ($allFiles->count()) {
//            $template->setVisible('media');
//            $images = array();
//            $files = array();
//            foreach ($allFiles as $file) {     // Sort files
//                if ($file->isImage()) $images[] = $file;
//                else $files[] = $file;
//            }
//            if (count($images)) {
//                $template->setVisible('image-list');
//                foreach ($images as $i => $file) {
//                    $filename = basename($file->getPath());
//                    $t = $template->getRepeat('image-block');
//                    $t->setAttr('image', 'src', $file->getUrl());
//                    $t->setAttr('image', 'alt', $filename);
//                    $t->appendHtml('image-caption', 'Fig ' . ($i+1) . '<br/>Name: '. $filename);
//                    $t->appendRepeat();
//                }
//            }
//            if (count($files)) {
//                $template->setVisible('file-list');
//                foreach ($files as $file) {
//                    $filename = basename($file->getPath());
//                    $t = $template->getRepeat('item');
//                    $t->setAttr('link', 'href', $file->getUrl());
//                    $t->appendText('link', $filename);
//                    $t->appendRepeat();
//                }
//            }
//
//        }





        $css = <<<CSS
@page {
 header: myHeader1;
 footer: myFooter1;
}
p {
    padding: 0px 0px;
    margin: 0.5em 0px 0em 0px;
    line-height: 1.3em;
}
h1, h2, h3, h4, h5, h6 {
  margin: 0.3em 0px 0em 0px;
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
}
table.border {
  border: 1px solid #CCC;
}
table.details td {
  padding: 4px 10px;
}
img.logo {
  width: 128px;
}
.textBlock {
  margin-top: 10px;
}
.image-block {
  border: 1px solid #CCC;
  margin-bottom: 10px;
  padding-top: 20px;
  text-align: center;
}
.image-block img {
}
.image-block figcaption {
  border-top: 1px solid #CCC;
  text-align: left;
  margin-top: 20px;
  padding: 10px;
  background-color: #EFEFEF;
}
CSS;
        $template->appendCss($css);
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

  <htmlpageheader name="myHeader1">
    <table width="100%" class="head-t">
      <tr>
        <td width="50%">Case Report</td>
        <td width="50%" style="text-align: right;" var="headerTitle"></td>
      </tr>
    </table>
  </htmlpageheader>
  
  <htmlpagefooter name="myFooter1">
    <table width="100%" class="head-t">
      <tr>
        <td width="50%">Date: {DATE j-m-Y}</td>
        <td width="50%" style="text-align: right;">{PAGENO}/{nbpg}</td>
      </tr>
    </table>
  </htmlpagefooter>


  <div class="content">
    <h2 style="text-align: center;" var="pageTitle"></h2>
    <div class="header">
      <table width="100%" style="" class="border">
        <tr>
          <td width="18%" style="">
            <img src="#" alt="Logo" class="logo" var="logo" choice="logo"/>
          </td>
          <td width="" style="padding-top: 20px;line-height: 1.4;"><span var="institution"></span></td>
          <td></td>
          <td width="40%" style="padding-top: 20px;line-height: 1.6;">
            Submission Date: <span var="submissionDate"></span><br/>
            Pathology Number: <span var="pathologyId"></span><br/>
            Submitter: <span var="clientName"></span>
          </td>
        </tr>
      </table>
      
      <br/>
      <table width="100%" style="" class="border details">
        <tr>
          <td width="50%"><b>Client Details:</b></td>
          <td width="50%"></td>
        </tr>
        <tr>
          <td><span var="ownerName"></span></td>
          <td>Phone: <span var="ownerPhone"></span></td>
        </tr>
        <tr>
          <td><span var="ownerAddress"></span></td>
          <td>Fax: <span var="ownerFax"></span></td>
        </tr>
        <tr>
          <td><span var="ownerCity"></span></td>
          <td>Email: <span var="ownerEmail"></span></td>
        </tr>
      </table>
      
      <br/>
      <table width="100%" style="" class="border details">
        <tr>
          <td width="50%"><b>Patient Details:</b></td>
          <td width="50%"></td>
        </tr>
        <tr>
          <td>Name: <span var="animalName"></span></td>
          <td>Patient #: <span var="patientNumber"></span></td>
        </tr>
        <tr>
          <td>Species: <span var="species"></span></td>
          <td>Breed: <span var="breed"></span></td>
        </tr>
        <tr>
          <td>Age: <span var="age"></span></td>
          <td>Sex: <span var="sex"></span></td>
        </tr>
      </table>
      <br/>
      
      
      <h3 style="text-align: center; margin: 0px;padding: 0;" var="name"></h3>
      
      <div class="textBlock" style="page-break-inside: avoid;" repeat="textBlock" var="textBlock">
        <h4 var="title"></h4>
        <div var="content"></div>
      </div>
    
      <div choice="pathologist" style="page-break-inside: avoid;margin-top: 20px;" >
        <p><b>Pathologist:</b></p>
        <p style="margin-left: 20px;">
          <span var="pathologistName"></span> <small var="pathologistCreds"></small><br/>
          <span var="pathologistPosition"></span>
        </p> 
      </div>
      
    </div>
    
      
      <pagebreak choice="media">
          <!-- Files and media -->      
          <div class="media">
            <div class="file-list" style="page-break-inside: avoid;" choice="file-list">
              <h4>Files:</h4>
              <ul var="list">
                <li var="item" repeat="item"><a href="#" target="_blank" var="link"></a></li>
              </ul>
            </div>
            <div class="image-list" choice="image-list">
              <h4>Images:</h4>
              <figure class="image-block" style="page-break-inside: avoid;" var="image-block" repeat="image-block">
                <img src="#" alt="" var="image" />
                <figcaption var="image-caption"></figcaption>
              </figure>
            </div>
            <p>&nbsp;</p>
          </div>
      </pagebreak>
    
  </div>
</body>
</html>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}