<?php
namespace App\Controller;

use Tk\Alert;
use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Terms extends \Bs\Controller\Iface
{

    /**
     * @var Form
     */
    protected $form = null;


    public function __construct()
    {
        $this->setPageTitle('Terms Of Use');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

    }

    /**
     * show()
     *
     * @return \Dom\Template
     * @throws \Exception
     */
    public function show()
    {
        $template = parent::show();


        return $template;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>
 
    <!-- Card Grid -->
    <div class="container content-space-t-3 content-space-t-lg-2">
      <div class="text-center mb-7">
        <h1 class="display-5">Terms Of Use.</h1>
         <p>{Under Construction}</p>
         <p>&nbsp;</p>
         <p>&nbsp;</p>
         <p>&nbsp;</p>
         <p>&nbsp;</p>
         <p>&nbsp;</p>
      </div>

      <div choice="hide" class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
        <div class="col mb-4 mb-lg-0">
          <!-- Card -->
          <div class="card card-lg text-center h-100">
            <div class="card-body">
              <div class="mb-3">
                <i class="bi-person-circle fs-1 text-dark"></i>
              </div>

              <div class="mb-5">
                <h4>Pre-visit inquiries</h4>
              </div>

              <div class="mb-5">
                <span class="d-block">Mon-Fri</span>
                <span class="d-block">9:30 AM to 6:00 PM Pacific</span>
              </div>

              <div class="d-grid mb-3">
                <a class="btn btn-white" href="mailto:support@site.com"><i class="bi-envelope-open me-2"></i> support@site.com</a>
              </div>

              <a class="btn btn-ghost-dark btn-sm" href="#"><i class="bi-telephone me-2"></i> (062) 8324923</a>
            </div>
          </div>
          <!-- End Card -->
        </div>
        <!-- End Col -->

        <div class="col mb-4 mb-lg-0">
          <!-- Card -->
          <div class="card card-lg text-center h-100">
            <div class="card-body">
              <div class="mb-3">
                <i class="bi-wallet2 fs-1 text-dark"></i>
              </div>

              <div class="mb-5">
                <h4>Billing questions</h4>
              </div>

              <div class="mb-5">
                <span class="d-block">Mon-Fri</span>
                <span class="d-block">9:30 AM to 5:00 PM Pacific</span>
              </div>

              <div class="d-grid mb-3">
                <a class="btn btn-white" href="mailto:billing@site.com"><i class="bi-envelope-open me-2"></i> billing@site.com</a>
              </div>

              <a class="btn btn-ghost-dark btn-sm" href="#"><i class="bi-telephone me-2"></i> (062) 1099222</a>
            </div>
          </div>
          <!-- End Card -->
        </div>
        <!-- End Col -->

        <div class="col">
          <!-- Card -->
          <div class="card card-lg text-center h-100">
            <div class="card-body">
              <div class="mb-3">
                <i class="bi-currency-exchange fs-1 text-dark"></i>
              </div>

              <div class="mb-5">
                <h4>Sales questions</h4>
              </div>

              <div class="mb-5">
                <span class="d-block">Mon-Fri</span>
                <span class="d-block">9:30 AM to 6:00 PM Pacific</span>
              </div>

              <div class="d-grid mb-3">
                <a class="btn btn-white" href="mailto:sale@site.com"><i class="bi-envelope-open me-2"></i> sale@site.com</a>
              </div>

              <a class="btn btn-ghost-dark btn-sm" href="#"><i class="bi-telephone me-2"></i> (062) 3383314</a>
            </div>
          </div>
          <!-- End Card -->
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Card Grid -->
    
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}