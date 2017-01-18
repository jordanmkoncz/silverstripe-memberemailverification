<?php

class EmailVerificationSecurityExtension extends Extension {
    private static $allowed_actions = array(
        // Actions
        'email_sent',
        'verify_email',
        'validate_email',

        // Forms
        'VerifyEmailForm',

        // Form actions
        'submitVerifyEmailForm'
    );

    public function email_sent(SS_HTTPRequest $request) {
        $email = Session::get('EmailVerificationEmail');
        $controller = $this->getResponseController(_t('MemberEmailVerification.EMAILSENTTITLE', "Email Verification Link Sent"));

        return $controller->customise(array(
            'Content' =>
                '<p>' .
                sprintf(_t('MemberEmailVerification.EMAILSENTTEXT', "An email verification link has been sent to %s, provided an account exists for this email address."), $email) .
                '</p>',
            'Email' => $email
        ))->renderWith(array('Security_email_sent', 'Security', 'Page'));
    }

    public function verify_email(SS_HTTPRequest $request) {
        $controller = $this->getResponseController(_t('MemberEmailVerification.VERIFYEMAILTITLE', "Verify Email"));

        return $controller->customise(array(
            'Content' =>
                '<p>' .
                _t('MemberEmailVerification.VERIFYEMAILTEXT',
                    "You need to verify your email address before you can log in. Use the form below to resend the email verification link.") .
                '</p>',
            'Form' => $this->owner->VerifyEmailForm()
        ))->renderWith(array('Security_verify_email', 'Security', 'Page'));
    }

    public function validate_email(SS_HTTPRequest $request) {
        $controller = $this->getResponseController(_t('MemberEmailVerification.VERIFYEMAILTITLE', "Verify Email"));
        $verification_string = $request->param('ID');

        $member = Member::get()->filter(array(
            'VerificationString' => $verification_string
        ))->first();

        if(!$member) {
            return $controller->customise(array(
                'Content' =>
                    '<p>' .
                    _t('MemberEmailVerification.VALIDATEEMAILFAIL',
                        "Email verification failed. This may be due to an incorrect verification string, please ensure you copy and paste the entire link. If this problem persists, please contact us.") .
                    '</p>'
            ))->renderWith(array('Security_validate_email_fail', 'Security', 'Page'));
        }

        $member->Verified = true;
        $member->write();

        return $controller->customise(array(
            'Content' =>
                '<p>' .
                _t(
                    'MemberEmailVerification.VALIDATEEMAILSUCCESS',
                    "Your email has been successfully verified. You can now {login_link} to the website.",
                    array(
                        'login_link' => '<a href="/Security/login">log in</a>'
                    )
                ) .
                '</p>'
        ))->renderWith(array('Security_validate_email_success', 'Security', 'Page'));
    }

    /**
     * Factory method for the Verify Email form.
     *
     * @return Form
     */
    public function VerifyEmailForm() {
        $email_field_label = singleton('Member')->fieldLabel(Member::config()->unique_identifier_field);
        $email_field = EmailField::create('Email', $email_field_label, null, null, $this)->setAttribute('autofocus', 'autofocus');

        $fields = FieldList::create(
            $email_field
        );

        $actions = FieldList::create(
            FormAction::create('submitVerifyEmailForm', _t('MemberEmailVerification.BUTTONRESENDEMAIL', "Send me the email verification link again"))
        );

        $form = new EmailVerificationLoginForm(
            $this->owner,
            'submitVerifyEmailForm',
            $fields,
            $actions,
            false
        );

        return $form;
    }

    /**
     * Submit action for the Verify Email form.
     *
     * @param $data
     * @return SS_HTTPResponse
     */
    public function submitVerifyEmailForm($data) {
        $controller = Controller::curr();
        $email = isset($data['Email']) ? $data['Email'] : false;
        $email = Convert::raw2sql($email);
        if( !($email && Email::is_valid_address($email)) ) {
            return $controller->redirect('/Security/verify-email');
        }

        $member = Member::get()->filter(array(
            'Email' => $email
        ))->first();

        if($member) {
            $member->sendVerificationEmail();
        }

        Session::set('EmailVerificationEmail', $email);

        return $controller->redirect('Security/email-sent/');
    }

    /**
     * Prepare the controller for handling the response to this request.
     *
     * Copied from Security getResponseController() with minor modifications.
     *
     * @param string $title Title to use
     * @return Controller
     */
    public function getResponseController($title) {
        $temp_page = new Page();
        $temp_page->Title = $title;
        $temp_page->URLSegment = 'Security';
        $temp_page->ID = -1 * rand(1,10000000); // Disable ID-based caching of the log-in page by making it a random number

        $controller = Page_Controller::create($temp_page);
        $controller->init();

        return $controller;
    }
}
