<?php
/**
 * Fonctions d'aide pour l'envoi d'emails
 */

/**
 * Envoie un email
 * 
 * @param string $to Adresse email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $message Corps de l'email (HTML)
 * @param string $from Adresse email de l'expéditeur (optionnel)
 * @return bool Succès ou échec de l'envoi
 */
function sendEmail($to, $subject, $messageHTML, $messageTEXT, $from = null) {
    if ($from === null) {
        $from = 'noreply@mediatek.mael-cv.me';
    }

    $boundary = md5(time());
    
    // En-têtes de l'email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $headers .= "From: MediaTek <$from>\r\n";
    $headers .= "Reply-To: contact@mediatek.mael-cv.me\r\n";
    $headers .= "X-Mailer: MediaTekMailer/1.0\r\n";

    // Corps de l'email avec version texte et HTML
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= $messageTEXT."\r\n\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $body .= $messageHTML."\r\n";
    $body .= "--$boundary--";
    
    if (!mail($to, $subject, $body, $headers)) {
        error_log("Échec d'envoi d'email à $to");
        return false;
    }
    return true;
}

/**
 * Génère un token unique
 * 
 * @return string Token unique
 */
function generateToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Envoie un email de vérification
 * 
 * @param string $to Adresse email du destinataire
 * @param string $token Token de vérification
 * @return bool Succès ou échec de l'envoi
 */
function sendVerificationEmail($to, $token) {
    $subject = "Vérification de votre adresse email - MediaTek";
    
    // URL de vérification (à ajuster selon votre configuration de serveur)
    $verificationUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/auth/verify_email.php?email=' . urlencode($to) . '&token=' . $token;
    
    $message = "
    <!DOCTYPE html>
<html lang='fr'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Your Verification Code</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      color: #333333;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    .header {
      background-color: #4361ee;
      padding: 24px;
      text-align: center;
      color: white;
    }
    .content {
      padding: 30px;
      line-height: 1.6;
    }
    .verification-code {
      background-color: #f0f3ff;
      border-radius: 6px;
      padding: 20px;
      text-align: center;
      margin: 25px 0;
      font-size: 32px;
      font-weight: bold;
      letter-spacing: 6px;
      color: #4361ee;
    }
    .footer {
      background-color: #f8f9fa;
      padding: 20px;
      text-align: center;
      font-size: 12px;
      color: #999999;
    }
    .button {
      display: inline-block;
      background-color: #4361ee;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      margin-top: 20px;
    }
    @media only screen and (max-width: 600px) {
      .container {
        width: 100%;
        border-radius: 0;
      }
      .content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>MediaTek</h1>
    </div>
    <div class='content'>
      <h2>Vérification de votre adresse email</h2>
      <p>Bonjour,</p>
      <p>Merci de vous être inscrit sur MediaTek. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
      
      
      <a href='$verificationUrl' class='button'>Verify Your Account</a>

      <p>Si vous n'avez pas créé de compte, vous pouvez ignorer cet email.</p>
    </div>
    <div class='footer'>
      <p>© 2025 MediaTek. All rights reserved.</p>
      <p>Maël-cv X EFREI Bordeaux</p>
      <p>This is an automated message, please do not reply.</p>
    </div>
  </div>
</body>
</html>
    ";
    $messageTEXT = "Veuillez cliquer sur le lien suivant pour vérifier votre adresse email: $verificationUrl";
    
    return sendEmail($to, $subject, $message, $messageTEXT);
}

/**
 * Envoie un email de réinitialisation de mot de passe
 * 
 * @param string $to Adresse email du destinataire
 * @param string $token Token de réinitialisation
 * @return bool Succès ou échec de l'envoi
 */
function sendPasswordResetEmail($to, $token) {
    $subject = "Réinitialisation de votre mot de passe - MediaTek";
    
    // URL de réinitialisation (à ajuster selon votre configuration de serveur)
    $resetUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/auth/reset_password.php?email=' . urlencode($to) . '&token=' . $token;
    
    $message = "
    <!DOCTYPE html>
<html lang='fr'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Your Verification Code</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      color: #333333;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    .header {
      background-color: #4361ee;
      padding: 24px;
      text-align: center;
      color: white;
    }
    .content {
      padding: 30px;
      line-height: 1.6;
    }
    .verification-code {
      background-color: #f0f3ff;
      border-radius: 6px;
      padding: 20px;
      text-align: center;
      margin: 25px 0;
      font-size: 32px;
      font-weight: bold;
      letter-spacing: 6px;
      color: #4361ee;
    }
    .footer {
      background-color: #f8f9fa;
      padding: 20px;
      text-align: center;
      font-size: 12px;
      color: #999999;
    }
    .button {
      display: inline-block;
      background-color: #4361ee;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      margin-top: 20px;
    }
    @media only screen and (max-width: 600px) {
      .container {
        width: 100%;
        border-radius: 0;
      }
      .content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>MediaTek</h1>
    </div>
    <div class='content'>
      <h2>Réinitialisation de votre mot de passe</h2>
      <p>Bonjour,</p>
      <p>Vous avez demandé la réinitialisation de votre mot de passe. Pour définir un nouveau mot de passe, veuillez cliquer sur le lien ci-dessous :</p>
      
      
      <a href='$resetUrl' class='button'>Réinitialiser mon mot de passe</a>
      
      <p>Si vous n'avez pas fait cette demande, vous pouvez ignorer cet email et votre mot de passe restera inchangé.</p>
        <p>Ce lien expirera dans 1 heure.</p>
    </div>
    <div class='footer'>
      <p>© 2025 MediaTek. All rights reserved.</p>
      <p>Maël-cv X EFREI Bordeaux</p>
      <p>This is an automated message, please do not reply.</p>
    </div>
  </div>
</body>
</html>
    ";

    $messageTEXT = "Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe: $resetUrl";
    
    return sendEmail($to, $subject, $message, $messageTEXT);
}

/**
 * Envoie un email avec le code de vérification pour la double authentification
 * 
 * @param string $to Adresse email du destinataire
 * @param string $code Code de vérification
 * @return bool Succès ou échec de l'envoi
 */
function sendLoginVerificationEmail($to, $code) {
    $subject = "Code de vérification pour votre connexion - MediaTek";
    
    $message = "
    <!DOCTYPE html>
<html lang='fr'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Your Verification Code</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f5f5;
      color: #333333;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    .header {
      background-color: #4361ee;
      padding: 24px;
      text-align: center;
      color: white;
    }
    .content {
      padding: 30px;
      line-height: 1.6;
    }
    .verification-code {
      background-color: #f0f3ff;
      border-radius: 6px;
      padding: 20px;
      text-align: center;
      margin: 25px 0;
      font-size: 32px;
      font-weight: bold;
      letter-spacing: 6px;
      color: #4361ee;
    }
    .footer {
      background-color: #f8f9fa;
      padding: 20px;
      text-align: center;
      font-size: 12px;
      color: #999999;
    }
    .button {
      display: inline-block;
      background-color: #4361ee;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      margin-top: 20px;
    }
    @media only screen and (max-width: 600px) {
      .container {
        width: 100%;
        border-radius: 0;
      }
      .content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class='container'>
    <div class='header'>
      <h1>MediaTek</h1>
    </div>
    <div class='content'>
      <h2>Vérification de connexion</h2>
      <p>Bonjour,</p>
      <p>Voici votre code de vérification pour finaliser votre connexion à MediaTek:</p>
      
      <div class='verification-code'>
        $code
      </div>
      
      <p>Ce code est valable pendant 10 minutes.</p>
        <p>Si vous n'avez pas tenté de vous connecter, veuillez ignorer cet email et sécuriser votre compte en changeant votre mot de passe.</p>
      <hr>
      <p>Si vous pensez que votre compte ou votre mot de passe est compromis vous pouvez reinitialiser le mot de passe en cliquant sur le bouton ci dessous</p>
      
      <a href='http://".$_SERVER['HTTP_HOST']."/auth/forgot_password.php' class='button'>Reset Password</a>
    </div>
    <div class='footer'>
      <p>© 2025 YourCompany. All rights reserved.</p>
      <p>123 Main Street, City, Country</p>
      <p>This is an automated message, please do not reply.</p>
    </div>
  </div>
</body>
</html>
    ";

    $messageTEXT = "Voici votre code de vérification pour vous connecter à MediaTek: $code";
    
    return sendEmail($to, $subject, $message, $messageTEXT);
}
/**
 * Envoie un email de confirmation d'emprunt
 * 
 * @param array $user Informations sur l'utilisateur
 * @param array $book Informations sur le livre
 * @param string $borrowDate Date d'emprunt
 * @param string $returnDate Date de retour prévue
 * @return bool Succès ou échec de l'envoi
 */
function sendBorrowConfirmationEmail($user, $book, $borrowDate, $returnDate) {
  $subject = "Confirmation d'emprunt - MediaTek";
  
  $message = "
  <html>
  <head>
      <title>Confirmation d'emprunt</title>
      <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          h1 { color: #4361ee; }
          .book-info { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .dates { background-color: #e6f7ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .important { background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .footer { font-size: 12px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
      </style>
  </head>
  <body>
      <div class='container'>
          <h1>Confirmation d'emprunt</h1>
          <p>Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",</p>
          <p>Nous vous confirmons l'emprunt du livre suivant :</p>
          
          <div class='book-info'>
              <h2>" . $book['title'] . "</h2>
              <p>Auteur : " . $book['author'] . "</p>
              <p>ISBN : " . $book['isbn'] . "</p>
          </div>
          
          <div class='dates'>
              <p><strong>Date d'emprunt :</strong> " . date('d/m/Y', strtotime($borrowDate)) . "</p>
              <p><strong>Date de retour prévue :</strong> " . date('d/m/Y', strtotime($returnDate)) . "</p>
          </div>
          
          <div class='important'>
              <p><strong>Important :</strong> Merci de retourner le livre avant la date indiquée pour éviter des pénalités.</p>
          </div>
          
          <p>Pour retourner le livre, connectez-vous à votre compte et rendez-vous dans la section 'Mon tableau de bord'.</p>
          
          <p>Bonne lecture !</p>
          
          <div class='footer'>
              <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
              <p>MediaTek - Bibliothèque numérique</p>
          </div>
      </div>
  </body>
  </html>
  ";

  $messageTEXT = "Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",\n\n" .
                "Nous vous confirmons l'emprunt du livre: " . $book['title'] . " (ISBN: " . $book['isbn'] . ").\n" .
                "Date d'emprunt: " . date('d/m/Y', strtotime($borrowDate)) . "\n" .
                "Date de retour prévue: " . date('d/m/Y', strtotime($returnDate)) . "\n\n" .
                "Important : Merci de retourner le livre avant la date indiquée pour éviter des pénalités.\n\n" .
                "Bonne lecture !\n\n" .
                "MediaTek - Bibliothèque numérique";
  
  return sendEmail($user['email'], $subject, $message, $messageTEXT);
}

/**
* Envoie un email de confirmation de demande de restitution
* 
* @param array $user Informations sur l'utilisateur
* @param array $book Informations sur le livre
* @param string $returnRequestDate Date de la demande de restitution
* @return bool Succès ou échec de l'envoi
*/
function sendReturnRequestEmail($user, $book, $returnRequestDate) {
  $subject = "Confirmation de demande de restitution - MediaTek";
  
  $message = "
  <html>
  <head>
      <title>Demande de restitution</title>
      <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          h1 { color: #4361ee; }
          .book-info { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .dates { background-color: #e6f7ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .note { background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .footer { font-size: 12px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
      </style>
  </head>
  <body>
      <div class='container'>
          <h1>Demande de restitution enregistrée</h1>
          <p>Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",</p>
          <p>Nous avons bien reçu votre demande de restitution pour le livre suivant :</p>
          
          <div class='book-info'>
              <h2>" . $book['title'] . "</h2>
              <p>Auteur : " . $book['author'] . "</p>
              <p>ISBN : " . $book['isbn'] . "</p>
          </div>
          
          <div class='dates'>
              <p><strong>Date de la demande :</strong> " . date('d/m/Y', strtotime($returnRequestDate)) . "</p>
          </div>
          
          <div class='note'>
              <p>Votre demande va être traitée par un administrateur.</p>
              <p>Vous recevrez un email de confirmation lorsque le retour sera validé.</p>
          </div>
          
          <p>Merci d'utiliser les services de MediaTek.</p>
          
          <div class='footer'>
              <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
              <p>MediaTek - Bibliothèque numérique</p>
          </div>
      </div>
  </body>
  </html>
  ";

  $messageTEXT = "Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",\n\n" .
                "Nous avons bien reçu votre demande de restitution pour le livre: " . $book['title'] . ".\n" .
                "Date de la demande: " . date('d/m/Y', strtotime($returnRequestDate)) . "\n\n" .
                "Votre demande va être traitée par un administrateur.\n" .
                "Vous recevrez un email de confirmation lorsque le retour sera validé.\n\n" .
                "Merci d'utiliser les services de MediaTek.\n\n" .
                "MediaTek - Bibliothèque numérique";
  
  return sendEmail($user['email'], $subject, $message, $messageTEXT);
}

/**
* Envoie un email de confirmation de restitution
* 
* @param array $user Informations sur l'utilisateur
* @param array $book Informations sur le livre
* @param string $returnDate Date de restitution
* @return bool Succès ou échec de l'envoi
*/
function sendReturnConfirmationEmail($user, $book, $returnDate) {
  $subject = "Confirmation de restitution - MediaTek";
  
  $message = "
  <html>
  <head>
      <title>Confirmation de restitution</title>
      <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
          .container { max-width: 600px; margin: 0 auto; padding: 20px; }
          h1 { color: #4361ee; }
          .book-info { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .dates { background-color: #e6f7ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
          .footer { font-size: 12px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
      </style>
  </head>
  <body>
      <div class='container'>
          <h1>Restitution confirmée</h1>
          <p>Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",</p>
          <p>Nous vous confirmons que la restitution du livre suivant a bien été enregistrée :</p>
          
          <div class='book-info'>
              <h2>" . $book['title'] . "</h2>
              <p>Auteur : " . $book['author'] . "</p>
              <p>ISBN : " . $book['isbn'] . "</p>
          </div>
          
          <div class='dates'>
              <p><strong>Date de restitution :</strong> " . date('d/m/Y', strtotime($returnDate)) . "</p>
          </div>
          
          <p>Nous vous remercions et espérons que vous avez apprécié votre lecture.</p>
          <p>N'hésitez pas à consulter notre catalogue pour découvrir d'autres livres qui pourraient vous intéresser.</p>
          
          <div class='footer'>
              <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
              <p>MediaTek - Bibliothèque numérique</p>
          </div>
      </div>
  </body>
  </html>
  ";

  $messageTEXT = "Bonjour " . $user['first_name'] . ' ' . $user['last_name'] . ",\n\n" .
                "Nous vous confirmons que la restitution du livre: " . $book['title'] . " a bien été enregistrée.\n" .
                "Date de restitution: " . date('d/m/Y', strtotime($returnDate)) . "\n\n" .
                "Nous vous remercions et espérons que vous avez apprécié votre lecture.\n" .
                "N'hésitez pas à consulter notre catalogue pour découvrir d'autres livres qui pourraient vous intéresser.\n\n" .
                "MediaTek - Bibliothèque numérique";
  
  return sendEmail($user['email'], $subject, $message, $messageTEXT);
}

?>