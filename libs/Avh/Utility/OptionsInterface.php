<?php
namespace Avh\Utility;



interface OptionsInterface
{


    public function addOptionFilters();

    public function clean($current_version = null);

    public function cleanOption($option_value, $current_version = null, $all_old_option_values = null);

    public function getDefaults();

    public function getOption($options = null);

    public function handleEnrichDefaults();

    public function handleTranslateDefaults();

    public function import($option_value, $current_version = null, $all_old_option_values = null);

    public function registerSetting();

    public function removeDefaultFilters();

    public function removeOptionFilters();

    public function validate($option_value);

}