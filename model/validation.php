<?php
function validEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}