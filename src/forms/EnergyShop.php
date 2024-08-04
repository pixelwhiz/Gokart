<?php

namespace pixelwhiz\minecart\forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pixelwhiz\minecart\entity\Minecart;

class EnergyShop {

    private static int $tax = 2500;
    private static int $price = 7650 / 2;

    public static function open(Player $player, Minecart $entity) : CustomForm {
        $minEnergy = (int)$entity->getEnergy();
        $maxEnergy = 100;
        $stepSlider = [];
        for ($i = $minEnergy; $i <= $maxEnergy; $i++) {
            $stepSlider[] = (string)$i;
        }

        $form = new CustomForm(function (Player $player, $data) use ($entity, $stepSlider) {
            if ($data === null || !isset($data[1])) {
                return false;
            }

            $index = (int)$data[1];
            if ($index < 0 || $index >= count($stepSlider)) {
                return false;
            }

            $amount = (int)$stepSlider[$index];
            if ($amount === (int)$entity->getEnergy()) {
                $player->sendMessage("No change in energy level.");
                return false;
            }

            $energy = (int)$entity->getEnergy();
            self::confirm($player, $energy, $amount);
        });

        $form->setTitle("Gas Station");
        $form->addLabel("Minecart ID: #".$entity->getId()."\nEnergy: ". (int)$entity->getEnergy() . "%%\n");
        $form->addStepSlider("Select Amount", $stepSlider);
        $form->sendToPlayer($player);
        return $form;
    }


    public static function confirm(Player $player, int $energy, int $amount) : ModalForm {
        $form = new ModalForm(function (Player $player, bool $data = false) use ($energy, $amount) {
            if (!is_bool($data)) {
                self::confirm($player, $energy, $amount);
                return false;
            }
            if ($data === true) {
                $player->sendMessage("You confirmed the purchase.");
            }
        });


        $form->setTitle("Gas Station");
        $price = ($amount - $energy) * self::$price;
        $tax = self::$tax;
        $total = number_format($price + $tax);
        $form->setContent(
            "Price: ".number_format($price)."\n" .
            "Tax: ".number_format($tax)."\n\n" .
            "Total: {$total} $\n\n" .
            "Click confirm button to purchase it."
        );
        $form->setButton1(TextFormat::GREEN ."Confirm");
        $form->setButton2("Cancel");
        $form->sendToPlayer($player);
        return $form;
    }
}