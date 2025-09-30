<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Passwort zurücksetzen</title>
</head>
    <body>
        <p>Hallo,</p>
        <p>du hast eine Anfrage zum Zurücksetzen deines Passworts gestellt.</p>
        <p>Klicke auf den folgenden Link, um dein Passwort neu zu setzen:</p>

        <p>
            <a href="{{ $url }}" style="background: #2563eb; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
                Passwort zurücksetzen
            </a>
        </p>

        <p>Oder kopiere diesen Link in deinen Browser:</p>
        <p>{{ $url }}</p>

        <p>Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren.</p>

        <p>Viele Grüße,<br>Dein App-Team</p>
    </body>
</html>
