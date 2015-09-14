<?php

namespace Sonus\Tree;
use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	use \Sonus\Tree\Traits\TreeTrait;

	/**
	 * @var string
	 */
	protected $table = 'role';

	/**
	 * @var string
	 */
	protected $node_left = 'lft';

	/**
	 * @var string
	 */
	protected $node_right = 'rgt';

	/**
	 * @var int
	 */
	protected $node_primary = 'id';
	/**
	 * @var string
	 */
	protected $node_name = 'name';

}
