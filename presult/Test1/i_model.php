<?php

/**
 * Interface i_model
 */
interface i_model
{
	public function __construct($id = null);

	public function set_field($field, $value);

	public function get_field($field);

	public function save();

	public function delete();

	public function id();
}