<?php

/**
 * TODO: return false on fail.
 */
 function createDBConnection($dbpath) {
    $db = new PDO("sqlite:$dbpath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

function readDB($db = null, $sql = null, $params = false) {
    if ($db == null || $sql == null) return false;
    try {
        if ($pdostmt = $db->prepare($sql)) {
            if ($pdostmt->execute($params)) {
                return $pdostmt->fetchAll();
            }
        }
    } catch(PDOException $pe) {
        //$this->log(__LINE__.': PDO Exception: '. $pe->getMessage());
    } catch(Exception $e) {
        //$this->log(__LINE__.': Exception: '.$e->getMessage());
    }
    return false;
}

?>