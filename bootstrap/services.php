<?php

$services = [];

$mysqliPool = new Swoole\Database\MysqliPool(
    (new Swoole\Database\MysqliConfig())
        ->withHost("mysql")
        ->withPort(3306)
        ->withDbName($_ENV["MYSQL_DATABASE"])
        ->withCharset('utf8mb4')
        ->withUsername("root")
        ->withPassword($_ENV["MYSQL_ROOT_PASSWORD"]));

$services['profile'] = new Proflie\Profile\ProfileController($mysqliPool);

// Admin
$services['admin'] = new Proflie\Admin\AuthenticatorRedirect(
    new Proflie\Admin\AdminController($mysqliPool, $_ENV['HOST']),
    $_ENV['JWT_SECRET'],
    $_ENV['HOST']
);
$services['profile_update'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\ProfileUpdateApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['profile_get'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\ProfileGetApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['links_get'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\LinksGetApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['link_put'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\LinkPutApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['link_delete'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\LinkDeleteApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['link_post'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\LinkPostApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['request_email_change'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\RequestEmailChangeApi(
        $_ENV["JWT_SECRET"],
        $_ENV["MAILJET_PUBLIC_KEY"],
        $_ENV["MAILJET_PRIVATE_KEY"],
        $mysqliPool,
        $_ENV['HOST']
    ),
    $_ENV["JWT_SECRET"],
);
$services['confirm_email_change'] = new Proflie\UpdateEmail\ConfirmEmailChangeController(
    $_ENV["JWT_SECRET"],
    $mysqliPool,
    $_ENV['HOST']
);
$services['username_update'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\UsernameUpdateApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);
$services['password_update'] = new Proflie\Admin\AuthenticatorUnauthorize(
    new Proflie\Admin\PasswordUpdateApi($mysqliPool),
    $_ENV["JWT_SECRET"],
);

// Signin
$services['home'] = new Proflie\Home\HomeController();
$services['signin'] = new Proflie\Home\SigninApi(
    $mysqliPool,
    $_ENV["JWT_SECRET"],
    $_ENV["RECAPTCHA_PRIVATE"],
    $_ENV['HOST'],
);

// Signup
$services['create_account_get'] = new Proflie\Signup\CreateAccountController($_ENV["JWT_SECRET"]);
$services['create_account_post'] = new Proflie\Signup\CreateAccountApi(
    $mysqliPool,
    $_ENV["JWT_SECRET"],
    $_ENV['HOST'],
);

$services['send_email_verification_get'] = new Proflie\Signup\SendEmailVerificationController();
$services['send_email_verification_post'] = new Proflie\Signup\SendEmailVerificationApi(
    $_ENV["JWT_SECRET"],
    $_ENV["MAILJET_PUBLIC_KEY"],
    $_ENV["MAILJET_PRIVATE_KEY"],
    $mysqliPool,
    $_ENV["RECAPTCHA_PRIVATE"],
    $_ENV["HOST"],
);
$services['email_sent'] = new Proflie\Signup\EmailSentController();

return $services;
