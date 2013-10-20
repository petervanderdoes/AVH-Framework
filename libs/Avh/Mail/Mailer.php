<?php
namespace Avh\Mail;

class Mailer
{

    /**
     * Sends an email using WordPress mail function wpmail.
     *
     * @param string $to
     * @param string $subject
     * @param array  $message
     *            Each entry of the array is a new line.
     * @param array $footer
     *            Optional footer in the email.
     */
    public static function sendMail($to, $subject, $message, array $footer = array())
    {
        // The blogname option is escaped with esc_html on the way into the database in sanitize_option
        // we want to reverse this for the plain text arena of emails.
        $_blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $subject = sprintf('[%s] ', $_blogname) . $subject;
        $message = array_merge($message, $footer);
        $msg = '';
        foreach ($message as $line) {
            $msg .= $line . "\r\n";
        }
        wp_mail($to, $subject, $msg);

        return;
    }
}
