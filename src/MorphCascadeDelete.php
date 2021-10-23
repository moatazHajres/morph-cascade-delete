<?php

namespace moatazHajres\MorphCascadeDelete;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;

trait MorphCascadeDelete {

    /**
     * @override
     *
     * deletes all related morph relations before deleting the actual model
     */
    public function delete() 
    {
        $allChildMorphRelations = $this->getModelMorphRelations();

        if(count($allChildMorphRelations['childMorphRelations'])) {
            foreach ($allChildMorphRelations['childMorphRelations'] as $morphRelation) {
                $this->$morphRelation()->delete();
            }
        }

        if(count($allChildMorphRelations['childMorphToManyRelations'])) {
            foreach ($allChildMorphRelations['childMorphToManyRelations'] as $morphToManyRelation) {
                $this->$morphToManyRelation()->detach();
            }
        }

        parent::delete();
    }

    /**
     *
     * get all model morph relations
     */
    public function getModelMorphRelations(): array
    {
        $model = new static;

        $modelReflection = new ReflectionClass($model);

        $modelMethods = $this->getModelPublicMethods($modelReflection);

        $childMorphRelations = $this->getChildMorphRelations($modelMethods, $model);

        $childMorphToManyRelations = $this->getChildMorphToManyRelations($modelMethods, $model);

        $allModelChildMorphRelations = ['childMorphRelations' => $childMorphRelations, 'childMorphToManyRelations' => $childMorphToManyRelations];

        return $allModelChildMorphRelations;
    }

    /**
     *
     * get all model public methods
     */
    protected function getModelPublicMethods(ReflectionClass $modelReflection): array
    {
        return $modelReflection->getMethods(ReflectionMethod::IS_PUBLIC);
    }

    /**
     *
     * get all child morph relations of type: morphOne or morphMany
     */
    protected function getChildMorphRelations(array $modelMethods, self $model): array
    {
        $morphRelations = [];

        foreach ($modelMethods as $method) {
            if($method->isFinal()) {
                if($this->isRelationMethod($method, $model)) {
                    if($this->isChildMorphRelation($method, $model)) {
                        array_push($morphRelations, $method->getName());
                    }
                }
            }
        }

        return $morphRelations;
    }

    /**
     *
     * get all child morph relations of type: morphToMany
     */
    protected function getChildMorphToManyRelations(array $modelMethods, self $model): array
    {
        $morphToManyRelations = [];

        foreach ($modelMethods as $method) {
            if($method->isFinal()) {
                if($this->isRelationMethod($method, $model)) {
                    if($this->isChildMorphToManyRelation($method, $model)) {
                        array_push($morphToManyRelations, $method->getName());
                    }
                }
            }
        }

        return $morphToManyRelations;
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
     * check if relation is a child morph relation of type: morphOne or morphMany
     */
    protected function isChildMorphRelation(ReflectionMethod $method, self $model): bool
    {
        return ($method->invoke($model) instanceof MorphOne || $method->invoke($model) instanceof MorphMany);
    }

    /**
     *
     * check if relation is a child morph relation of type: morphToMany
     */
    protected function isChildMorphToManyRelation(ReflectionMethod $method, self $model): bool
    {
        return $method->invoke($model) instanceof MorphToMany;
    }
}