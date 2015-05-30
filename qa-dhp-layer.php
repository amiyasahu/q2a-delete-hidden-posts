<?php
    if ( !defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
        header('Location: ../../');
        exit;
    }

    class qa_html_theme_layer extends qa_html_theme_base
    {

        function q_view_buttons($q_view)
        {
            if (isset($q_view['form']['buttons']) && count($q_view['form']['buttons'])) {
                ami_dhp_add_q_delete_button($q_view['form']['buttons'], $q_view['raw']);
            }

            qa_html_theme_base::q_view_buttons($q_view);
        }

        function a_item_buttons($a_item)
        {
            if (isset($a_item['form']['buttons']) && count($a_item['form']['buttons'])) {
                ami_dhp_add_a_delete_button($a_item['form']['buttons'], $a_item['raw']);
            }

            qa_html_theme_base::a_item_buttons($a_item);
        }

        function c_item_buttons($c_item)
        {
            if (isset($c_item['form']['buttons']) && count($c_item['form']['buttons'])) {
                ami_dhp_add_c_delete_button($c_item['form']['buttons'], $c_item['raw']);
            }

            qa_html_theme_base::c_item_buttons($c_item);
        }

        function head_script(){
            parent::head_script();
            $this->output('<script>');
            $this->output('function dhp_ask_user_confirmation(event){');
                $this->output('if(!confirm("'.dhp_lang('are_you_sure').'")) {');
                $this->output('event.preventDefault();');
                    $this->output('return false ;');
                $this->output('}');
                $this->output('else {');
                    $this->output('return true ;');
                $this->output('}');
            $this->output('}');
            $this->output('</script>');
            $this->output();
        }

    }
    /*
        Omit PHP closing tag to help avoid accidental output
    */