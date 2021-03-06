<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CartBundle\Provider;

use Sylius\Bundle\CartBundle\Model\CartInterface;
use Sylius\Bundle\CartBundle\Model\CartManagerInterface;
use Sylius\Bundle\CartBundle\Storage\CartStorageInterface;

/**
 * Default provider cart.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class CartProvider implements CartProviderInterface
{
    /**
     * Cart identifier storage.
     *
     * @var CartStorageInterface
     */
    protected $storage;

    /**
     * Cart manager.
     *
     * @var CartManagerInterface
     */
    protected $cartManager;

    /**
     * Cart.
     *
     * @var CartInterface
     */
    protected $cart;

    /**
     * Constructor.
     *
     * @param CartStorageInterface $storage
     * @param CartManagerInterface $cartManager
     */
    public function __construct(CartStorageInterface $storage, CartManagerInterface $cartManager)
    {
        $this->storage = $storage;
        $this->cartManager = $cartManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart()
    {
        if (null == $this->cart) {
            $cartIdentifier = $this->storage->getCurrentCartIdentifier();

            if ($cartIdentifier) {
                $cart = $this->getCartByIdentifier($cartIdentifier);

                if ($cart) {
                    $this->cart = $cart;
                    return $cart;
                }
            }

            $cart = $this->cartManager->createCart();
            $this->cartManager->persistCart($cart);
            $this->storage->setCurrentCartIdentifier($cart);

            $this->cart = $cart;
        }

        return $this->cart;
    }

    /**
     * {@inheritdoc}
     */
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        $this->storage->setCurrentCartIdentifier($cart);
    }

    /**
     * {@inheritdoc}
     */
    public function abandonCart()
    {
        $this->cart = null;
        $this->storage->resetCurrentCartIdentifier();
    }

    /**
     * Gets cart by cart identifier.
     *
     * @param mixed $identifier
     * @return CartInterface|null
     */
    protected function getCartByIdentifier($identifier)
    {
        return $this->cartManager->findCart($identifier);
    }
}
