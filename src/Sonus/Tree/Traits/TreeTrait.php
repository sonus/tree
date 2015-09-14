<?php

namespace Sonus\Tree\Traits;

trait TreeTrait {

	public function insertNode($string, $node, $child = true) {

		\DB::beginTransaction();
		$node = self::where($this->node_primary, $node)
			->first();
		if ($child && sizeof($node)) {
			$position = $node->{$this->node_left};
		} else if (sizeof($node)) {
			$position = $node->{$this->node_right};
		} else {
			$position = 0;
		}
		self::where($this->node_right, '>', $position)
			->increment($this->node_right, 2);
		self::where($this->node_left, '>', $position)
			->increment($this->node_left, 2);

		$role = new self();
		$role->{$this->node_name} = $string;
		$role->{$this->node_left} = $position + 1;
		$role->{$this->node_right} = $position + 2;
		$role->save();
		\DB::commit();
	}

	public function updateNode($id, $node, $child = true) {
		//    Update
		\DB::beginTransaction();
		$node = self::where($this->node_primary, $id)
			->first();
		$parent_node = self::where($this->node_primary, $id)
			->first();
		if (sizeof($node) && sizeof($parent_node)) {
			$left = $node->{$this->node_left};
			$right = $node->{$this->node_right};
			$parent_left = $parent_node->{$this->node_left};
			$parent_right = $parent_node->{$this->node_right};

			$width = intval($right - $left) + 1;
			$left_width = $left + $width;
			$right_width = $right + $width;
			$old_place = "update {$this->table}
			set {$this->node_left} = {$this->node_left} - {$width},  {$this->node_right} = {$this->node_right} - {$width}
			where
			{$this->node_left}>={$left_width} || {$this->node_right}>{$right_width}";

			if ($child) {
				$parent_width = intval($left - $parent_left) + 1;
			} else {
				$parent_width = intval($right - $parent_left) + 1;
			}
			$new_place = "update {$this->table}
			set {$this->node_left} = {$this->node_left} - {$parent_width},  {$this->node_right} = {$this->node_right} - {$parent_width}
			where {$this->node_left}>={$left} && {$this->node_right}<={$right}";

			\DB::statement($new_place);
			\DB::statement($old_place);
		}
		\DB::commit();
	}

	public function deleteNode($node, $keep_child = false) {
		//    Delete
		\DB::beginTransaction();
		$node = self::where($this->node_primary, $node)
			->first();
		if (sizeof($node)) {
			$left = $node->{$this->node_left};
			$right = $node->{$this->node_right};
			$width = intval($right - $left) + 1;
			if ($keep_child) {
				self::where($this->node_left, $left)->delete();

				self::whereBetween($this->node_left, [$left, $right])
					->decrement($this->node_left);
				self::whereBetween($this->node_left, [$left, $right])
					->decrement($this->node_right);
				self::where($this->node_right, '>', $right)
					->decrement($this->node_right, 2);
				self::where($this->node_left, '>', $right)
					->decrement($this->node_left, 2);
			} else {
				self::whereBetween($this->node_left, [$left, $right])->delete();

				self::where($this->node_right, '>', $right)
					->decrement($this->node_right, $width);
				self::where($this->node_left, '>', $right)
					->decrement($this->node_left, $width);
			}
		}
		\DB::commit();
	}
}
