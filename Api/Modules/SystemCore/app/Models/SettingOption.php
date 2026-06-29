<?php

namespace Modules\SystemCore\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class SettingOption extends Model
{
    use DeleteTranslations;

    // Specify the table associated with the model
    protected $table = 'setting_options';

    // Specify which fields can be mass assigned
    protected $fillable = ['option_name', 'option_value', 'autoload'];

    public $translationKeys = [
        'option_name','com_site_title',
        'com_site_subtitle',
        'com_meta_title',
        'com_meta_description',
        'com_meta_tags',
        'com_og_title',
        'com_og_description',
        'com_maintenance_title',
        'com_maintenance_description',
        'com_site_full_address',
        'com_site_contact_number',
        'com_site_footer_copyright',
        'com_register_page_title',
        'com_register_page_subtitle',
        'com_register_page_description',
        'com_register_page_terms_title',
        'com_login_page_title',
        'com_login_page_subtitle',
        'com_product_details_page_delivery_title',
        'com_product_details_page_delivery_subtitle',
        'com_product_details_page_return_refund_title',
        'com_product_details_page_return_refund_subtitle',
        'theme_one',
        'theme_two',
    ];
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }


    public $timestamps = true;
}
