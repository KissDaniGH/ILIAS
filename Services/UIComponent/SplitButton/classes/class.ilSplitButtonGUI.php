<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/SplitButton/classes/class.ilButtonToSplitButtonMenuItemAdapter.php';
require_once 'Services/UIComponent/SplitButton/classes/class.ilSplitButtonItemDivider.php';
require_once 'Services/UIComponent/SplitButton/exceptions/class.ilSplitButtonException.php';
require_once "Services/UIComponent/Button/classes/class.ilButton.php";

/**
 * Class ilSplitButton
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesUIComponent
 */
class ilSplitButtonGUI extends ilButton
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilButton
	 */
	protected $default_button;

	/**
	 * @var ilSplitButtonMenuItem[]
	 */
	protected $menu_items = array();

	/**
	 * 
	 */
	public function __construct()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$this->lng = $lng;
	}

	/**
	 * @return self;
	 */
	public static function getInstance()
	{
		return new self(self::TYPE_SPLIT);
	}

	/**
	 * @param ilSplitButtonMenuItem $menu_item
	 */
	public function addMenuItem(ilSplitButtonMenuItem $menu_item)
	{
		$this->menu_items[] = $menu_item;
	}

	/**
	 * @param ilSplitButtonMenuItem $menu_item
	 */
	public function removeMenuItem(ilSplitButtonMenuItem $menu_item)
	{
		$key = array_search($menu_item, $this->menu_items);
		if($key !== false)
		{
			unset($this->menu_items[$key]);
		}
	}

	/**
	 * @return boolean
	 */
	public function hasMenuItems()
	{
		return count($this->menu_items) > 0;
	}

	/**
	 * @return ilSplitButtonMenuItem[]
	 */
	public function getMenuItems()
	{
		return $this->menu_items;
	}

	/**
	 * @param ilSplitButtonMenuItem[] $menu_items
	 * @throws ilSplitButtonException
	 */
	public function setMenuItems($menu_items)
	{
		array_walk($menu_items, function(&$item, $idx) {
			if(!($item instanceof ilSplitButtonMenuItem))
			{
				throw new ilSplitButtonException(sprintf(
					"Cannot set menu items, element at index '%s' is not of type 'ilSplitButtonItem'", $idx
				));
			}
		});

		$this->menu_items = $menu_items;
	}

	/**
	 * @return ilButton
	 */
	public function getDefaultButton()
	{
		return $this->default_button;
	}

	/**
	 * @return boolean
	 */
	public function hasDefaultButton()
	{
		return ($this->default_button instanceof ilButton);
	}

	/**
	 * @param ilButton $default_button
	 */
	public function setDefaultButton(ilButton $default_button)
	{
		$this->default_button = $default_button;
	}

	/**
	 * @return string
	 * @throws ilSplitButtonException
	 */
	public function render()
	{
		$tpl = new ilTemplate('tpl.split_button.html', true, true, 'Services/UIComponent/SplitButton');

		if(!$this->hasDefaultButton())
		{
			throw new ilSplitButtonException(
				"Cannot render a split button without a default button"
			);
		}

		$tpl->setVariable('DEFAULT_ITEM_CONTENT', $this->getDefaultButton()->render());
		if($this->hasMenuItems())
		{
			$btn_classes = $this->getDefaultButton()->getCSSClasses();
			if($this->getDefaultButton()->isPrimary())
			{
				$btn_classes[] = 'btn-primary';
			}
			$tpl->setVariable('BTN_CSS_CLASS', implode(' ', $btn_classes));

			foreach($this->getMenuItems() as $item)
			{
				if($item instanceof ilSplitButtonSeparatorMenuItem)
				{
					$tpl->setCurrentBlock('separator');
					$tpl->touchBlock('separator');
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock('item');
					$tpl->setVariable('CONTENT', $item->getContent());
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock('items');
				$tpl->parseCurrentBlock();
			}

			$tpl->setVariable('TXT_TOGGLE_DROPDOWN', $this->lng->txt('toggle_dropdown'));
		}

		return $tpl->get();
	}
}