<?php

namespace Twist\Library\Model;

interface RelationshipInterface
{

	public function has_parent(): bool;

	public function parent(): ?ModelInterface;

	public function has_children(): bool;

	public function children(): ?CollectionInterface;

}