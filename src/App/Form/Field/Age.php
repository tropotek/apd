<?php
namespace App\Form\Field;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Age extends \Tk\Form\Field\Iface
{
    protected $dobName = 'dob';

    protected $dodName = 'dod';

    protected $ageMName = 'age_m';


    /**
     * __construct
     *
     * @param string $age
     * @param string $ageM Age in months
     */
    public function __construct($age = 'age', $ageM = 'age_m')
    {
        parent::__construct($age);
        $this->ageMName = $ageM;
        //$this->setLabel(ucwords($age) . '/' . ucwords($ageM));
    }


    /**
     * @param array|\ArrayObject $values
     * @return $this
     */
    public function load($values)
    {
        $v = array();
        if (isset($values[$this->getName()])) {
            $v[$this->getName()] =  $values[$this->getName()];
        }
        if (isset($values[$this->ageMName])) {
            $v[$this->ageMName] =  $values[$this->ageMName];
        }
        if (!count($v)) $v = null;

        if (!$v && !empty($values[$this->dobName])) {
            $dob = \Tk\Date::createFormDate($values[$this->dobName]);
            $dod = \Tk\Date::create();
            if (!empty($values[$this->dodName])) {
                $dod = \Tk\Date::createFormDate($values[$this->dodName]);
            }
            $v[$this->getName()] = $dob->diff($dod)->y;
            $v[$this->ageMName] = $dob->diff($dod)->m;
        }


        $this->setValue($v);
        return $this;
    }

    
    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function show()
    {
        $t = $this->getTemplate();

        $this->decorateElement($t, 'year');
        $this->decorateElement($t, 'month');

        $t->setAttr('age-input', 'data-dob-target', '#'.$this->getForm()->getId().'-'.$this->dobName);
        $t->setAttr('age-input', 'data-dod-target', '#'.$this->getForm()->getId().'-'.$this->dodName);

        $t->setAttr('year', 'name', $this->getName());
        $t->setAttr('month', 'name', $this->ageMName);

        $t->setAttr('year', 'id', $this->getId().'_'.$this->getName());
        $t->setAttr('month', 'id', $this->getId().'_'.$this->ageMName);

        // Set the input type attribute

        // Set the field value
        $value = $this->getValue();
        if (is_array($value)) {
            if (isset($value[$this->getName()]))
                $t->setAttr('year', 'value', $value[$this->getName()]);
            if (isset($value[$this->ageMName]))
                $t->setAttr('month', 'value', $value[$this->ageMName]);
        }

        $js = <<<JS
jQuery(function($) {

  $('.tk-age-input').each(function () {
    var el = $(this);
    var dobEl = $(el.data('dobTarget'));
    var dob = dobEl.data("datetimepicker").getDate();
    var dodEl = $(el.data('dodTarget'));
    var dod = dodEl.data("datetimepicker").getDate(); // Auto assigns nnow() when empty
    
    var yearEl = el.find('input[name="age"]');
    var monthEl = el.find('input[name="age_m"]');
    
    function updateAge() {
      var months = (parseInt(yearEl.val()) * 12) + parseInt(monthEl.val());
      var newDob = dod.clone();
      newDob.setMonth(newDob.getMonth() - months);
      
      // Update the el values
      dobEl.data("datetimepicker").setDate(newDob);
      yearEl.val(Math.floor(months/12));
      monthEl.val(months%12);
    }
    function updateDates() {
      dob = dobEl.data("datetimepicker").getDate();
      dod = dodEl.data("datetimepicker").getDate();
      if (dob.getTime() > dod.getTime()) {
        alert('DOD must be > DOB');
        return;
      }
      var years = dod.getFullYear() - dob.getFullYear();
      var months = dod.getMonth() - dob.getMonth();
      yearEl.val(years);
      monthEl.val(months);
      updateAge();
    }
    
    el.find('input').on('change', function () {
      updateAge();
    });
    dobEl.on('change', function () {
      updateDates();
    })
    dodEl.on('change', function () {
      updateDates();
    })
    
    
  });
  
});
JS;
        $t->appendJs($js);


        return $t;
    }

    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {

        $xhtml = <<<HTML
<div class="input-group tk-age-input" var="age-input">
    <div class="input-group-prepend" title="Years">
      <span class="input-group-text">Y</span>
    </div>
    <input type="text" class="form-control" var="year" title="Years" />
    <div class="input-group-prepend" title="Months">
      <span class="input-group-text">M</span>
    </div>
    <input type="text" class="form-control" var="month" title="Months" />
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
    
    
}