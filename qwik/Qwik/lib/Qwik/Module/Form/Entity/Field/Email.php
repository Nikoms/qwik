<?php
namespace Qwik\Module\Form\Entity\Field;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Champ E-mail
 */

class Email extends Base
{

    public function getConstraints()
    {
        $constraints = parent::getConstraints();
        $constraints[] = new Assert\Email();
        return $constraints;
    }
}