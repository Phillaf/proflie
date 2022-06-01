<?php

declare(strict_types=1);

return FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/robots.txt', 'robots');
    // Admin
    $r->addRoute('GET', '/admin', 'admin');
    $r->addRoute('POST', '/profile', 'profile_update');
    $r->addRoute('GET', '/profile', 'profile_get');
    // Admin-Links
    $r->addRoute('GET', '/links', 'links_get');
    $r->addRoute('PUT', '/link/{id:\d+}', 'link_put');
    $r->addRoute('POST', '/link', 'link_post');
    $r->addRoute('DELETE', '/link/{id:\d+}', 'link_delete');
    // Admin-Account
    $r->addRoute('POST', '/request-email-change', 'request_email_change');
    $r->addRoute('GET', '/confirm-email-change/{emailToken}', 'confirm_email_change');
    $r->addRoute('POST', '/username', 'username_update');
    $r->addRoute('POST', '/password', 'password_update');
    // Signin
    $r->addRoute('GET', '/', 'home');
    $r->addRoute('POST', '/signin', 'signin');
    // Signup
    $r->addRoute('GET', '/send-email-verification', 'send_email_verification_get');
    $r->addRoute('POST', '/send-email-verification', 'send_email_verification_post');
    $r->addRoute('GET', '/email-sent', 'email_sent');
    $r->addRoute('GET', '/account/{token}', 'create_account_get');
    $r->addRoute('POST', '/account', 'create_account_post');
});
