<?php
namespace App\Controller;

use Tk\Request;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Index extends \Uni\Controller\Index
{

    
    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        parent::doDefault($request);

    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $template->insertText('site-title', $this->getConfig()->get('site.title'));


//        if ($this->getConfig()->getInstitutionMapper()->findActive()->count() > 1) {
//            $template->setVisible("multiInstitutions");
//        } else {
//            $template->setAttr('institution-login', 'href', $this->getConfig()->getInstitution()->getLoginUrl());
//            $template->setVisible("login");
//        }

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
<div>
  <!-- Hero -->
  <div class="overflow-hidden">
    <div class="container content-space-t-1 content-space-t-lg-2">
      <div class="row justify-content-center align-items-lg-center">
        <div class="col-lg-7 mb-7 mb-sm-10 mb-lg-0">
          <div class="pe-lg-5">
            <div class="mb-4">
              <h1 class="display-4">Anatomic Pathology Database</h1>
              <p class="fs-3">Lab reporting and client tracking software. Contact us today for a demo</p>
            </div>

            <div class="mb-7">
              <a class="btn btn-primary btn-pointer" href="/contact.html">Request a demo</a>
            </div>

            <div class="row">
              <div class="col-sm-auto">
                <div class="pe-sm-4">
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                </div>
              </div>
              <!-- End Col -->

              <div class="col-sm-auto">
                <div class="ps-sm-4">
                  <p>&nbsp;</p>
                  <p>&nbsp;</p>
                </div>
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->
          </div>
        </div>
        <!-- End Col -->

        <div class="col-sm-9 col-lg-5">
          <div class="position-relative">
            <!-- Card -->
            <div class="card card-shadow mb-3">
              <div class="card-body">
                <div class="text-center mb-5">
                  <h4 class="card-title">Monthly Pricing</h4>
                </div>

                <!-- List -->
                <ul class="list-unstyled list-py-1">
                  <li>
                    <!-- Radio Check -->
                    <label class="form-check form-check-reverse form-check-select form-check-pinned-top-end" for="formCheckSelect1">
                      <input type="radio" class="form-check-input" name="formCheckSelect" id="formCheckSelect1" />
                      <span class="form-check-label">
                          <span class="fw-medium">Basic</span>
                          <span class="d-block h4 mb-0">AU $500</span>
                          <span class="d-block fs-6 text-muted">All the basics</span>
                        </span>
                      <span class="form-check-stretched-bg"></span>
                    </label>
                    <!-- End Radio Check -->
                  </li>

                  <li>
                    <!-- Radio Check -->
                    <label class="form-check form-check-reverse form-check-select form-check-pinned-top-end" for="formCheckSelect2">
                      <input type="radio" class="form-check-input" name="formCheckSelect" id="formCheckSelect2" checked="checked" />
                      <span class="form-check-label">
                          <span class="fw-medium">Professional <span class="badge bg-soft-primary text-primary rounded-pill">Most popular</span></span>
                          <span class="d-block h4 mb-0">AU $850</span>
                          <span class="d-block fs-6 text-muted">Additional development options</span>
                        </span>
                      <span class="form-check-stretched-bg"></span>
                    </label>
                    <!-- End Radio Check -->
                  </li>

                  <li>
                    <!-- Radio Check -->
                    <label class="form-check form-check-reverse form-check-select form-check-pinned-top-end" for="formCheckSelect3">
                      <input type="radio" class="form-check-input" name="formCheckSelect" id="formCheckSelect3" />
                      <span class="form-check-label">
                          <span class="fw-medium">Enterprise</span>
                          <span class="d-block h4 mb-0">AU $1250</span>
                          <span class="d-block fs-6 text-muted">Advanced features for scaling to your needs</span>
                        </span>
                      <span class="form-check-stretched-bg"></span>
                    </label>
                    <!-- End Radio Check -->
                  </li>
                </ul>
                <!-- End List -->

                <div class="d-grid">
                  <a class="btn btn-primary" href="/contact.html">Request A Demo</a>
                </div>
              </div>
            </div>
            <!-- End Card -->

            <!-- SVG Shape -->
            <figure class="position-absolute top-0 end-0 zi-n1 d-none d-sm-block me-n10" style="width: 4rem;">
              <img class="img-fluid" src="/html/public/assets/svg/components/pointer-up.svg" alt="Image Description" />
            </figure>
            <!-- End SVG Shape -->
          </div>

          <div class="text-center">
            <p class="fs-5 text-muted">Need custom plan? <a class="link link-pointer" href="/contact.html">Contact sales</a></p>
          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
  </div>
  <!-- End Hero -->

  <!-- Features -->
  <div class="overflow-hidden content-space-t-2 content-space-t-lg-3">
    <div class="container position-relative content-space-2 content-space-lg-3">
      <div class="row">
        <div class="col-lg-5 align-self-lg-end mb-7 mb-lg-0">
          <h2>For pathologist and lab technicians working as a team</h2>
          <div class="d-none d-lg-flex justify-content-center mt-7" style="width: 15rem;">
            <img class="img-fluid" src="/html/public/assets/svg/illustrations/plane.svg" alt="Image Description" />
          </div>
        </div>
        <!-- End Col -->

        <div class="col-lg-7 align-self-lg-center">
          <div class="row">
            <div class="col-lg-6 mb-4">
              <!-- Card -->
              <div class="card card-shadow h-100">
                <div class="card-body">
                  <div class="mb-3">
                    <i class="bi-emoji-smile fs-2 text-dark"></i>
                  </div>
                  <h4>Hosting</h4>
                  <p class="mb-0">We host and maintain the software and servers.</p>
                </div>
              </div>
              <!-- End Card -->
            </div>
            <!-- End Col -->

            <div class="col-lg-6 mb-4">
              <!-- Card -->
              <div class="card card-shadow h-100">
                <div class="card-body">
                  <div class="mb-3">
                    <i class="bi-droplet fs-2 text-dark"></i>
                  </div>
                  <h4>Be more focused</h4>
                  <p class="mb-0">Let us manage the system upgrades and software security.</p>
                </div>
              </div>
              <!-- End Card -->
            </div>
            <!-- End Col -->

            <div class="w-100"></div>

            <div class="col-lg-6 mb-4 mb-lg-0">
              <!-- Card -->
              <div class="card card-shadow h-100">
                <div class="card-body">
                  <div class="mb-3">
                    <i class="bi-briefcase fs-2 text-dark"></i>
                  </div>
                  <h4>Built for business</h4>
                  <p class="mb-0">Functionality your customers actually want.</p>
                </div>
              </div>
              <!-- End Card -->
            </div>
            <!-- End Col -->

            <div class="col-lg-6">
              <!-- Card -->
              <div class="card card-shadow h-100">
                <div class="card-body">
                  <div class="mb-3">
                    <i class="bi-speedometer2 fs-2 text-dark"></i>
                  </div>
                  <h4>Built for speed</h4>
                  <p class="mb-0">72% faster loading speed compared to traditional websites.</p>
                </div>
              </div>
              <!-- End Card -->
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->

      <div class="position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5"></div>
    </div>
  </div>
  <!-- End Features -->

  <div class="overflow-hidden content-space-t-2 content-space-t-lg-3"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}