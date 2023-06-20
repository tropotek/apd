<?php
namespace App\Controller;

use Tk\Alert;
use Tk\Encrypt;
use Tk\Request;
use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;
use Uni\Uri;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Contact extends \Bs\Controller\Iface
{

    /**
     * @var Form
     */
    protected $form = null;


    public function __construct()
    {
        $this->setPageTitle('Contact Us');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        if ($this->getConfig()->getInstitution() && $this->getConfig()->getInstitution()->getDomain() == Uri::create()->getHost()) {
            // Redirect to main site contact form
            Uri::create('https://'.$this->getConfig()->get('site.domain').'/contact.html')->redirect();
        }

        $this->form = new Form('contactForm');

        $nc = $this->getSession()->get('nc');
        if (!$request->request->has('nc') || !$this->getSession()->has('nc')) {
            $ts = time();
            $nc = array(
                'ts' => $ts,
                'nc' => Encrypt::create('CT&%&%^gFGBF$^' . $ts)->encode(md5($ts))
            );
            $this->getSession()->set('nc', $nc);
        }

        $this->form->appendField(new Field\Hidden('nc'))->setValue($nc['nc']);
        $this->form->appendField(new Field\Hidden('email'));
        $this->form->appendField(new Field\Input('firstName'));
        $this->form->appendField(new Field\Input('lastName'));
        $this->form->appendField(new Field\Input('contact'));
        $this->form->appendField(new Field\Input('company'));
        $this->form->appendField(new Field\Input('website'));
        $this->form->appendField(new Field\Textarea('message'));


        $this->form->appendField(new Event\Submit('send', array($this, 'doSubmit')))->addCss('btn-primary');
        
        $this->form->execute();

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

        if ($this->getConfig()->get('google.map.apikey')) {
            $template->setAttr('googleMap', 'src', \Tk\Uri::create('https://www.google.com/maps/embed/v1/search?q=Melbourne')->set('key', $this->getConfig()->get('google.map.apikey')));
        }

        // Render the form
        $ren = new \Tk\Form\Renderer\DomStatic($this->form, $template);
        $ren->show();

        return $template;
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function doSubmit($form)
    {
        $values = $form->getValues();

        $nc = $this->getSession()->get('nc');
        //vd($nc, time() - (60*10));
        if (!$nc || empty($values['nc']) || ($nc['ts'] < time() - (60*10))) {
            $form->addError('Invalid form submission. Please try again.');
        }

        // Bot detection
        if (!empty($values['email'])) {
            $form->addFieldError('Invalid form submission');
        }

        if (empty($values['firstName'])) {
            $form->addFieldError('firstName', 'Please enter your first name');
        }
        if (empty($values['company'])) {
            $form->addFieldError('company', 'Please enter your company name');
        }
        if (empty($values['contact']) || !filter_var($values['contact'], \FILTER_VALIDATE_EMAIL)) {
            $form->addFieldError('contact', 'Please enter a valid email address');
        }
        if (empty($values['message'])) {
            $form->addFieldError('message', 'Please enter some message text');
        }

        if ($this->form->hasErrors()) {
            return;
        }

        if ($this->sendEmail($form)) {
            \Tk\Alert::addSuccess('<strong>Success!</strong> Your form has been sent.');
            $this->getSession()->remove('nc');
        } else {
            \Tk\Alert::addError('<strong>Error!</strong> Something went wrong and your message has not been sent.');
        }

        \Tk\Uri::create()->redirect();
    }


    /**
     * @param Form $form
     * @return bool
     * @throws \Exception
     */
    private function sendEmail($form)
    {
        $name = strip_tags($form->getFieldValue('firstName') . ' ' . $form->getFieldValue('lastName'));
        $email = strip_tags($form->getFieldValue('contact'));
        $company = strip_tags($form->getFieldValue('company'));
        $web = strip_tags($form->getFieldValue('website'));
        $messageStr = strip_tags($form->getFieldValue('message'));

        $content = <<<HTML
<p>
Dear $name,
</p>
<p>
Email: $email<br/>
Company: $company<br/>
www: $web
</p>
<p>Message:<br/>
  $messageStr
</p>
HTML;

        $message = $this->getConfig()->createMessage();
        $message->addTo($email);
        $message->setSubject($this->getConfig()->get('site.title') . ':  Contact Form Submission - ' . $name);
        $message->set('content', $content);
        // TODO: Dissabled untill I can confirm if this is causing spam
        //return $this->getConfig()->getEmailGateway()->send($message);
        return true;
    }

    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div>

   
    <!-- Card Grid -->
    <div choice="hide" class="container content-space-t-3 content-space-t-lg-2">
      <div class="text-center mb-7">
        <h1 class="display-5">How can we help?</h1>
      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
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

    <!-- Contact Form -->
    <div class="overflow-hidden">
      <div class="container content-space-1 content-space-lg-2">
        <div class="row">
          <div class="col-lg-6 mb-10 mb-lg-0">
            <div class="pe-lg-10">
              <div class="mb-5">
                <h3>Our office</h3>
              </div>

              <div class="mb-5">
                <img class="img-fluid" src="/html/public/assets/img/580x480/img3.jpg" alt="Image Description" />
              </div>

              <!-- Info -->
<!--              <address>-->
<!--                <span class="d-block fs-3 fw-bold text-dark mb-2">UK:</span>-->
<!--                300 Bath Street<br />-->
<!--                Tay House<br />-->
<!--                Glasgow G2 4JR<br />-->
<!--                United Kingdom-->
<!--              </address>-->
              <!-- End Info -->
            </div>
          </div>
          <!-- End Col -->

          <div class="col-lg-6">
            <div class="position-relative">
              <!-- Card -->
              <div class="card card-lg">
                <!-- Card Body -->
                <div class="card-body">
                  <h4 class="mb-4">Contact us today</h4>

                  <form id="contactForm" method="post">
                    <input type="hidden" name="email" value="" />
                    
                    <div class="row">
                      <div class="col-sm-6 mb-4 mb-sm-0">
                        <!-- Form -->
                        <div class="mb-4">
                          <label class="form-label" for="contactsFormFirstName">First name</label>
                          <input type="text" class="form-control" name="firstName" id="contactsFormFirstName" />
                        </div>
                        <!-- End Form -->
                      </div>
                      <!-- End Col -->

                      <div class="col-sm-6">
                        <!-- Form -->
                        <div class="mb-4">
                          <label class="form-label" for="contactsFormLasttName">Last name</label>
                          <input type="text" class="form-control" name="lastName" id="contactsFormLastName"/>
                        </div>
                        <!-- End Form -->
                      </div>
                      <!-- End Col -->
                    </div>
                    <!-- End Row -->

                    <div class="row">
                      <div class="col-sm-6 mb-4 mb-sm-0">
                        <!-- Form -->
                        <div class="mb-4">
                          <label class="form-label" for="contactsFormCompany">Company</label>
                          <input type="text" class="form-control" name="company" id="contactsFormCompany" />
                        </div>
                        <!-- End Form -->
                      </div>
                      <!-- End Col -->

                      <div class="col-sm-6">
                        <!-- Form -->
                        <div class="mb-4">
                          <label class="form-label" for="contactsFormCompanyWebsite">Company website</label>
                          <input type="text" class="form-control" name="website" id="contactsFormCompanyWebsite" />
                        </div>
                        <!-- End Form -->
                      </div>
                      <!-- End Col -->
                    </div>
                    <!-- End Row -->

                    <!-- Form -->
                    <div class="mb-4">
                      <label class="form-label" for="contactsFormWorkEmail">Work email</label>
                      <input type="text" class="form-control" name="contact" id="contactsFormWorkEmail" />
                    </div>
                    <!-- End Form -->

                    <!-- Form -->
                    <div class="mb-4">
                      <label class="form-label" for="contactsFormDetails">Details</label>
                      <textarea class="form-control" name="message" id="contactsFormDetails" placeholder="Tell us about your requirements" aria-label="Tell us about your requirements" rows="4"></textarea>
                    </div>
                    <!-- End Form -->

                    <div class="d-grid">
                      <button type="submit" class="btn btn-primary btn-lg">Send inquiry</button>
                    </div>
                  </form>
                </div>
                <!-- End Card Body -->
              </div>
              <!-- End Card -->

              <!-- SVG Shape -->
              <figure class="position-absolute bottom-0 end-0 zi-n1 d-none d-md-block mb-n10" style="width: 15rem; margin-right: -8rem;">
                <img class="img-fluid" src="/html/public/assets/svg/illustrations/grid-grey.svg" alt="Image Description" />
              </figure>
              <!-- End SVG Shape -->

              <!-- SVG Shape -->
              <figure class="position-absolute bottom-0 end-0 d-none d-md-block me-n5 mb-n5" style="width: 10rem;">
                <img class="img-fluid" src="/html/public/assets/svg/illustrations/plane.svg" alt="Image Description" />
              </figure>
              <!-- End SVG Shape -->
            </div>
          </div>
          <!-- End Col -->
        </div>
        <!-- End Row -->
      </div>
    </div>
    <!-- End Contact Form -->
    
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}