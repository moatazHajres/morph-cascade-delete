<?php

namespace moatazHajres\MorphCascadeDelete;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;

trait MorphCascadeDelete {

    /**
     * @override
     *
     * deletes all related morph relations before deleting the actual model
     */
    public function delete() {

        $childMorphRelations = $this->getModelRelations();

        if(count($childMorphRelations)) {
            foreach ($childMorphRelations as $relation) {
                $this->$relation()->delete();
            }
        }

        parent::delete();
    }

    /**
     *
     * get all model relations
     */
    public function getModelRelations(): array
    {
        $model = new static;

        $modelReflection = new ReflectionClass($model);

        $modelMethods = $this->getModelFinalMethods($modelReflection);

        $childMorphRelations = $this->getChildMorphRelations($modelMethods, $model);

        return $childMorphRelations;
    }

    /**
     *
     * get all model public methods
     */
    protected function getModelFinalMethods(ReflectionClass $modelReflection): array
    {
        return $modelReflection->getMethods(ReflectionMethod::IS_PUBLIC);
    }

    /**
     *
     * get all child morph relations
     */
    protected function getChildMorphRelations(array $modelMethods, self $model): array
    {
        $morphManyRelations = [];

        foreach ($modelMethods as $method) {
            if($method->isFinal()) {
                if($this->isRelationMethod($method, $model)) {
                    if($this->isChildMorphRelation($method, $model)) {
                        array_push($morphManyRelations, $method->getName());
                    }
                }
            }
        }

        return $morphManyRelations;
    }

    /**
     *
     * check if method is a relation method
     */
    protected function isRelationMethod(ReflectionMethod $method, self $model): bool
    {
        return ($method->getNumberOfParameters() === 0 && $method->invoke($model) instanceof Relation);
    }

    /**
     *
     * check if relation is a child morph relation
     */
    protected function isChildMorphRelation(ReflectionMethod $method, self $model): bool
    {
        return ($method->invoke($model) instanceof MorphMany || $method->invoke($model) instanceof MorphOne);
    }
}