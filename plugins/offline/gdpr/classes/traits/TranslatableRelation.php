<?php

namespace OFFLINE\GDPR\Classes\Traits;

use Illuminate\Support\Facades\DB;

/**
 * @see https://github.com/rainlab/translate-plugin/issues/209#issuecomment-362088300
 */
trait TranslatableRelation
{
    /**
     * This is a temporary fix until
     * https://github.com/rainlab/translate-plugin/issues/209
     * is resolved.
     */
    protected function setTranslatableFields()
    {
        if ( ! post('RLTranslate') || !$this->model) {
            return;
        }

        foreach (post('RLTranslate') as $key => $value) {
            $data = collect($value)->intersectByKeys(array_flip($this->translatable));

            $obj = DB::table('rainlab_translate_attributes')
                     ->where('locale', $key)
                     ->where('model_id', $this->id)
                     ->where('model_type', get_class($this->model));

            if ($obj->count() > 0) {
                return $obj->update(['attribute_data' => $data->toJson()]);
            }

            return DB::table('rainlab_translate_attributes')
                     ->insert([
                             'locale'         => $key,
                             'model_id'       => $this->id,
                             'model_type'     => get_class($this->model),
                             'attribute_data' => $data->toJson(),
                         ]
                     );
        }
    }

    public function afterSave()
    {
        $this->setTranslatableFields();
    }
}