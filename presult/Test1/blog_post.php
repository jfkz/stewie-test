<?php

/**
 * Class blog_post
 */
class blog_post extends model
{
	const ID   = 'id';
	const NAME = 'name';
	const DATE = 'date';
	const TEXT = 'text';

	/**
	 * @return mixed
	 */
	public function save()
	{
		if ($this->get_field(self::DATE) === null) {
			$this->set_field(self::DATE, date('Y-m-d H:i:s'));
		}

		return parent::save();
	}

}
