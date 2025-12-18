<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Mot de passe temporaire</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="background: #f5f7fa; font-family: 'Segoe UI', Arial, sans-serif; margin:0; padding:0;">
    <div
        style="max-width: 480px; margin: 40px auto; background: #fff; border-radius: 18px; box-shadow: 0 8px 32px rgba(18,40,73,0.10); overflow: hidden;">
        @include('mails.partials.logo')
        <div style="padding: 36px 28px 28px 28px;">
            <h2 style="color: #122849; margin-top: 0; font-size: 1.6em; font-weight: 700;">Bonjour {{ $user->name }},
            </h2>
            <p style="color: #444; font-size: 1.08em; margin-bottom: 18px;">Vous avez demandé la réinitialisation de
                votre mot de passe. Voici votre mot de passe temporaire :</p>
            <div
                style="background: #f0f4fa; border-radius: 10px; padding: 18px 0; text-align: center; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(18,40,73,0.07);">
                <span
                    style="font-size: 1.4em; font-weight: bold; color: #122849; letter-spacing: 2px;">{{ $temporaryPassword }}</span>
            </div>
            <a href="{{ $frontendUrl }}"
                style="display: block; text-align: center; background: #122849; color: #fff; text-decoration: none; font-weight: 600; border-radius: 8px; padding: 14px 0; font-size: 1.08em; box-shadow: 0 2px 8px rgba(18,40,73,0.07); margin-bottom: 18px;">
                Se connecter
            </a>
            <p style="color: #888; font-size: 0.98em; margin-top: 24px;">Merci de vous connecter et de modifier votre
                mot de passe dès que possible.<br>
        </div>
    </div>
</body>

</html>
</body>

</html>
