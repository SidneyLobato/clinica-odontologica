<?php
// Nome de sessão próprio — evita conflito quando este sistema e outros
// (ex: TechStore) rodam em subdomínios diferentes de .localhost no mesmo
// navegador, já que alguns navegadores tratam cookies de *.localhost
// como compartilhados entre os subdomínios.
if (session_status() === PHP_SESSION_NONE) {
    session_name('PREVDENTISTAS_SESSID');
    session_start();
}
