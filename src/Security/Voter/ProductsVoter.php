<?php

namespace App\Security\Voter;

use App\Entity\Products;
use phpDocumentor\Reflection\Types\Self_;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $product): bool
    {
       if(!in_array($attribute, [self::EDIT, self::DELETE])){
           return false;
       }
       if (!$product instanceof Products) {
           return false;
       }
       return true;
       //return in_array($attribute, [self::EDIT, self::DELETE]) && $product instanceof Products;
    }
    protected function voteOnAttribute(string $attribute, $product, TokenInterface $token): bool
    {
        //On recupere l'utilisateur a partir du token
        $user = $token->getUser();

        if(!$user instanceof UserInterface) return false;

        // On verifie si l'utilisateur est admin
        if($this->security->isGranted('ROLE_ADMIN')) return true;

        //On verifie les permissions
        switch ($attribute){
            case self::EDIT:
                //on verifie si l'user peut éditer
                return $this->canEdit();
                break;
            case self::DELETE:
                //on verifie si user peut supprimer
                return $this->canDelete();
                break;
        }
    }

    private function canEdit(){
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }
    private function canDelete(){
        return $this->security->isGranted('ROLE_PRODUCT_ADMIN');
    }


}