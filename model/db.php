<?php

/*
 * Path to the database connection file.
 * Since we're hosting this on our own domains, this will be different for each person.
 * This was the best solution we could come up with without requiring each person to configure the file differently.
 * Originally we attempted to use /home/../db_haveyouall.php, but the ../ did not work.
 */
const DB_PATH = '../../../db_haveyouall.php';

function getDatabase() {
    require_once DB_PATH;
    return $cnxn;
}