<?php

class EmailVerificationMemberExtension extends DataExtension {
    private static $db = array(
        'Verified' => 'Boolean',
        'VerificationString' => 'Varchar(32)',
        'VerificationEmailSent' => 'Boolean'
    );

    private static $defaults = array(
        'Verified' => false,
        'VerificationEmailSent' => false
    );

    /**
     * Return whether the user is verified.
     *
     * @return string
     */
    public function IsVerified() {
        return ($this->owner->Verified) ? _t('Boolean.YESANSWER', 'Yes') : _t('Boolean.NOANSWER', 'No');
    }

    /**
     * Update Member summary fields to include whether the Member is verified.
     *
     * @param array $fields
     */
    public function updateSummaryFields(&$fields) {
        $fields['IsVerified'] = _t('MemberEmailVerification.EMAILVERIFIEDTITLE', "Email Verified");
    }

    /**
     * Check if the user has verified their email address.
     *
     * @param ValidationResult $result
     * @return ValidationResult
     */
    public function canLogIn(&$result) {
        if (!$this->owner->Verified) {
            // Don't require administrators to be verified
            if(Permission::checkMember($this->owner, 'ADMIN')) {
                return $result;
            }

            $result->error(_t('MemberEmailVerification.ERROREMAILNOTVERIFIED', 'Sorry, you need to verify your email address before you can log in.'));
        }

        return $result;
    }

    /**
     * Set VerificationString if not set, and send verification email if not sent.
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();

        if (!$this->owner->VerificationString) {
            $verification_string = md5(uniqid($this->owner->Email, true));

            $this->owner->VerificationString = $verification_string;
        }

        if (!$this->owner->Verified && !$this->owner->VerificationEmailSent) {
            $this->owner->VerificationEmailSent = $this->owner->sendVerificationEmail();
        }
    }

    /**
     * Send verification email to member.
     */
    public function sendVerificationEmail() {
        $validation_link = Controller::join_links(Director::absoluteURL('Security/validate-email'), $this->owner->VerificationString);
        $site_config = SiteConfig::current_site_config();
        $site_title = $site_config->Title;
        $admin_email = Config::inst()->get('Email', 'admin_email');

        $email_template_data = array(
            'Member' => $this->owner,
            'ValidationLink' => $validation_link,
            'SiteConfig' => $site_config
        );

        $email_subject = _t(
            'MemberEmailVerification.VERIFICATIONEMAILSUBJECT',
            "{site_title} Email Verification",
            array(
                'site_title' => $site_title
            )
        );

        if(!$admin_email) {
            // Fallback to a placeholder admin email if Email.admin_email is not set
            $admin_email = 'admin@domain.com';
        }

        $sender_email = self::get_formatted_email($site_title, $admin_email);
        $recipient_email = $this->owner->Email;

        $email_to_recipient = Email::create($sender_email, $recipient_email, $email_subject);
        $email_to_recipient->setTemplate('VerificationEmail');
        $email_to_recipient->populateTemplate($email_template_data);

        $email_status = $email_to_recipient->send();

        // Return true if the email was successfully sent
        // Mailer::email will return `true` or an array if the email was successfully sent
        if($email_status === true || is_array($email_status)) {
            return true;
        }

        return false;
    }

    /**
     * Format the email address so that it shows as being sent by the given name.
     *
     * @param string $name
     * @param string $email_address
     *
     * @return string
     */
    public static function get_formatted_email($name, $email_address) {
        return $name . ' <' . $email_address . '>';
    }
}