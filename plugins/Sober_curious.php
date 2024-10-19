<?php
namespace Telebot\Plugins;
include_once(__DIR__.'/db.functions.php');
/**
 * Write to database when person is !sober.
 * Count days. How many days person has been sober.
 * How many consecutive days.
 * @author LAama1
 * @date 2022-04-24
 */

class Sober_curious extends Template {


    public function handle(array $args = []): string {
        return '';
    }


}

?>
