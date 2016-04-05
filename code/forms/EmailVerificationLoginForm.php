<?php

class EmailVerificationLoginForm extends MemberLoginForm {
    protected $authenticator_class = 'EmailVerificationAuthenticator';

    /**
     * EmailVerificationLoginForm is the same as MemberLoginForm with the following changes:
     *  - The code has been cleaned up.
     *  - A form action for users who have lost their verification email has been added.
     *
     * We add fields in the constructor so the form is generated when instantiated.
     *
     * @param Controller $controller The parent controller, necessary to create the appropriate form action tag.
     * @param string $name The method on the controller that will return this form object.
     * @param FieldList|FormField $fields All of the fields in the form - a {@link FieldList} of {@link FormField} objects.
     * @param FieldList|FormAction $actions All of the action buttons in the form - a {@link FieldList} of {@link FormAction} objects
     * @param bool $checkCurrentUser If set to TRUE, it will be checked if a the user is currently logged in, and if so, only a logout button will be rendered
     */
    function __construct($controller, $name, $fields = null, $actions = null, $checkCurrentUser = true) {
        $email_field_label = singleton('Member')->fieldLabel(Member::config()->unique_identifier_field);
        $email_field = TextField::create('Email', $email_field_label, null, null, $this)->setAttribute('autofocus', 'autofocus');

        $password_field = PasswordField::create('Password', _t('Member.PASSWORD', 'Password'));
        $authentication_method_field = HiddenField::create('AuthenticationMethod', null, $this->authenticator_class, $this);
        $remember_me_field = CheckboxField::create('Remember', 'Remember me next time?', true);

        if ($checkCurrentUser && Member::currentUser() && Member::logged_in_session_exists()) {
            $fields = FieldList::create(
                $authentication_method_field
            );

            $actions = FieldList::create(
                FormAction::create('logout', _t('Member.BUTTONLOGINOTHER', "Log in as someone else"))
            );
        }
        else {
            if (!$fields) {
                $fields = FieldList::create(
                    $authentication_method_field,
                    $email_field,
                    $password_field
                );

                if (Security::config()->remember_username) {
                    $email_field->setValue(Session::get('SessionForms.MemberLoginForm.Email'));
                }
                else {
                    // Some browsers won't respect this attribute unless it's added to the form
                    $this->setAttribute('autocomplete', 'off');
                    $email_field->setAttribute('autocomplete', 'off');
                }
            }

            if (!$actions) {
                $actions = FieldList::create(
                    FormAction::create('doLogin', _t('Member.BUTTONLOGIN', "Log in")),
                    new LiteralField(
                        'forgotPassword',
                        '<p id="ForgotPassword"><a href="Security/lostpassword">'
                        . _t('Member.BUTTONLOSTPASSWORD', "I've lost my password") . '</a></p>'
                    ),
                    new LiteralField(
                        'resendEmail',
                        '<p id="ResendEmail"><a href="Security/verify-email">' . _t('MemberEmailVerification.BUTTONLOSTVERIFICATIONEMAIL', "I've lost my verification email") . '</a></p>'
                    )
                );
            }
        }

        if (isset($_REQUEST['BackURL'])) {
            $fields->push(HiddenField::create('BackURL', 'BackURL', $_REQUEST['BackURL']));
        }

        // Reduce attack surface by enforcing POST requests
        $this->setFormMethod('POST', true);

        parent::__construct($controller, $name, $fields, $actions);

        $this->setValidator(RequiredFields::create(
            'Email',
            'Password'
        ));
    }
}