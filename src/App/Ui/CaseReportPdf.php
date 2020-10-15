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
        parent::__construct('',
            $this->pathCase->getPathologyId() . ' - ' . ucwords($this->pathCase->getType())
        );
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

        $template->appendText('pageTitle', 'Veterinary Anatomic Pathology');

        $inst = sprintf('%s<br/>%s<br/>%s<br/>%s<br/>%s<br/>%s',
            $institution->getName(),
            $institution->getStreet(),
            $institution->getCity() . ' ' . $institution->getState() . ' ' . $institution->getPostcode(),
            'Phone: ' . $institution->getPhone(),
            'Fax: ' . $institution->getData()->get('fax'),
            'Email: ' . $institution->getEmail()
        );
        //$template->replaceHtml('institution', $inst);
        $template->appendHtml('institution', $inst);

        $template->appendText('submissionDate', $this->pathCase->getCreated(\Tk\Date::FORMAT_SHORT_DATE));
        $template->appendText('pathologyId', $this->pathCase->getPathologyId());
        $template->appendText('clientName', $this->pathCase->getClient()->getName());

        $owner = $this->pathCase->getOwner();
        if ($owner) {
            $template->appendText('ownerName', $owner->getName());
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
        if ($this->pathCase->getGrossMorphologicalDiagnosis()) {
            $block = $template->getRepeat('textBlock');
            $block->appendText('title', 'Gross Morphological Diagnosis:');
            $block->appendHtml('content', $this->pathCase->getGrossMorphologicalDiagnosis());
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
            $template->appendText('pathologistTitle', $pathologist->getData()->get('title'));
            $template->appendText('pathologistName', $pathologist->getName());
            $template->appendText('pathologistCreds', $pathologist->getData()->get('credentials'));
            $template->appendText('pathologistPosition', $pathologist->getData()->get('position'));
            $template->setVisible('pathologist');
        }
        $template->appendText('date', \Tk\Date::create()->format(\Tk\Date::FORMAT_SHORT_DATE));


        $css = <<<CSS
body {
  font-size: 0.8em;
}
table td {
  vertical-align: top;
}
img.logo {
  width: 128px;
}
.textBlock {
  margin-top: 10px;
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
  <div class="content">
    <h3 style="text-align: center;" var="pageTitle"></h3>
    <div class="header">
      <table width="100%" style="border: 1px solid #CCC;">
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
      <table width="100%" style="border: 1px solid #CCC;" >
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
      <table width="100%" style="border: 1px solid #CCC;">
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
      
      
      <div class="textBlock" style="page-break-inside: avoid;" repeat="textBlock" var="textBlock">
        <h3 var="title"></h3>
        <div var="content"></div>
      </div>
    </div>  
    <p>&nbsp;</p>
    <p choice="pathologist" style="page-break-inside: avoid;" >
      <b>Pathologist:</b><br/>
      <span var="pathologistTitle"></span> <span var="pathologistName"></span> <span var="pathologistCreds"></span><br/>
      <span var="pathologistPosition"></span> 
    </p>
    <p>Date: <span var="date"></span></p>
  </div>
</body>
</html>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}