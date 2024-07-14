<?php
namespace App\Chung;

/*
    암호화용 변수
*/

class EncVars
{
    /**
     * 계약상태
     *
     * @var array
     */
    public static $arrayAllCol = array(
        "users" => array(
            "name"
            , "ssn11", "ssn12"
            , "ph32", "ph32", "ph33", "ph34"
            , "ph22", "ph22", "ph23"
            , "ph12", "ph12", "ph13"
            , "zip"
            , "addr11", "addr12"
            , "email"
        ),
        "users_log" => array(
            "name"
            , "ph32", "ph32", "ph33", "ph34"
            , "ph22", "ph22", "ph23"
            , "ph12", "ph12", "ph13"
            , "zip"
            , "addr11", "addr12"
            , "email"
        ),
        "loan_app" => array(
            "name"
            , "ssn"
        ),
        "loan_app_extra" => array(
            "ph11", "ph12", "ph13", "ph1_name"
            , "ph21", "ph22", "ph23", "ph2_name"
            , "ph31", "ph32", "ph33"
            , "ph41", "ph42", "ph43", "ph4_name"
            , "addr11", "addr12"
            , "addr21", "addr22"
            , "addr31", "addr32"
            , "addr41", "addr42"
            , "old_addr11"
            , "old_addr21"
            , "old_addr31"
            , "old_addr41"
            , "owner_name"
            , "owner_name_eng"
            , "owner_ssn"
            , "com_addr11"
            , "com_addr12"
            , "com_ph"
            , "ceo_ssn"
            , "bon_com_addr11"
            , "bon_com_addr12"
            , "bon_com_ph"
        ),
        "loan_app_guarantor" => array(
            "name"
            , "ssn"
            , "ph11" , "ph12", "ph13", "ph1_name"
            , "ph21" , "ph22", "ph23", "ph2_name"
            , "ph31" , "ph32", "ph33"
            , "ph41" , "ph42", "ph43", "ph4_name"
            , "addr11", "addr12"
            , "addr21", "addr22"
            , "addr31", "addr32"
            , "addr41", "addr42"
        ),
        "cust_info" => array(
            "name"
            , "ssn"
        ),
        "cust_info_extra" => array(
            "ph11", "ph12", "ph13", "ph1_name"
            , "ph21" , "ph22", "ph23", "ph2_name"
            , "ph31" , "ph32", "ph33"
            , "ph41" , "ph42", "ph43", "ph4_name"
            , "ph51" , "ph52", "ph53"
            , "addr11", "addr12"
            , "addr21", "addr22"
            , "addr31", "addr32"
            , "addr41", "addr42"
            , "house_own_name"
            , "old_addr11"
            , "old_addr21"
            , "old_addr31"
            , "old_addr41"
        ),
        "cust_info_log" => array(
            "name"
            , "ssn"
            , "ph11" , "ph12", "ph13"
            , "ph21" , "ph22", "ph23"
            , "ph31" , "ph32", "ph33"
            , "ph41" , "ph42", "ph43"
            , "addr11", "addr12"
            , "addr21", "addr22"
            , "addr31", "addr32"
            , "addr41", "addr42"
            , "post_addr11", "post_addr12"
            , "old_addr11"
            , "old_addr21"
            , "old_addr31"
            , "old_addr41"
        ),
        "loan_info" => array(
            "loan_bank_ssn"
        ),
        "loan_info_guarantor" => array(
            "name"
            , "ssn"
            , "ph11" , "ph12", "ph13", "ph1_name"
            , "ph21" , "ph22", "ph23", "ph2_name"
            , "ph31" , "ph32", "ph33"
            , "ph41" , "ph42", "ph43", "ph4_name"
            , "addr11", "addr12"
            , "addr21", "addr22"
            , "addr31", "addr32"
            , "addr41", "addr42"
            , "old_addr11"
            , "old_addr21"
            , "old_addr31"
            , "old_addr41"
        ),
        "loan_info_log" => array(
            "name"
        ),
        "vir_acct" => array(
            "vir_acct_ssn"
            , "bank_owner"
            , "mo_ssn"
        ),
        "vir_acct_mo" => array(
            "mo_ssn"
        ),
        "cms_bank" => array(
            "cms_account"
        ),
        "ipcc_counsel" => array(
            "name"
            , "ssn1"
            , "ssn2"
            , "home_addr1"
            , "home_addr2"
            , "office_addr1"
            , "office_addr2"
            , "home_phone"
            , "office_phone"
            , "mobile_phone"
            , "car_owner_name"
            , "security_addr1"
            , "security_addr2"
            , "security_owner_name"
        ),
        "cb_master" => array(
            "name"
            , "ssn"
            , "ph11"
            , "ph12"
            , "ph13"
            , "ph21"
            , "ph22"
            , "ph23"
            , "ph31"
            , "ph32"
            , "ph33"
            , "ph41"
            , "ph42"
            , "ph43"
            , "name_eng"
            , "loan_bank_ssn"
            , "loan_bank_name"
        ),
        "separate" => array(
            "ssn", "name"
        ),
        "close_data" => array(
            "ssn", "name", "com_ssn", "com_name", "ceo_name"
            , "ph11" , "ph12", "ph13"
            , "ph21" , "ph22", "ph23"
            , "ph31" , "ph32", "ph33", "ph34"
            , "ph41" , "ph42", "ph43"
            , "name_eng", "loan_bank_ssn" , "loan_bank_name"
            , "dambo_addr11", "dambo_addr12"
        ),
        "close_data_dambo" => array(
            "ssn", "com_ssn", "com_name", "ceo_name"
        ),
        "close_data_guarantor" => array(
            "ssn", "name", "com_ssn", "com_name", "ceo_name"
        ),
        "kcb_0700_integration" => array(
            "ssn", "name"
        ),
        "nice_0700_integration" => array(
            "ssn", "name"
        ),
        "kfb_bf9011" => array(
            "ssn"
            , "com_name", "name", "com_ssn"
            , "rel_ssn1" , "rel_name1", "rel_ssn2"
            , "rel_name2" , "rel_ssn3", "rel_name3"
            , "rel_ssn4" , "rel_name4", "rel_ssn5"
            , "rel_name5"
        ),
        "kfb_bf9044" => array(
            "after_ssn"
            , "before_ssn", "after_com_name"
            , "after_name", "after_com_ssn"
            , "before_rel_ssn", "after_rel_ssn"
            , "after_rel_name" 
        ),
        "kfb_dg9011" => array(
            "ssn", "com_name", "name", "m_ssn"
        ),
        "kfb_dg9033" => array(
            "after_ssn"
            , "before_ssn", "after_com_name"
            , "after_name" , "before_m_ssn"
            , "after_m_ssn" , "ssn"
        ),
        "kfb_ln9011" => array(
            "ssn", "com_name", "name"
        ),
        "kfb_ln9033" => array(
            "after_ssn", "before_ssn", "after_com_name", "after_name"
        ),
        "kfb_ln9077" => array(
            "ssn", "corporate_name", "name", "registration_ph"
        ),
        "nice_1f003" => array(
            "ssn11", "ssn12", "ssn", "name"
        ),
        "nice_1f003_info38" => array(
            "change_ssn", "change_name"
        ),
        "nice_1f005" => array(
            "ssn11", "ssn12", "ssn", "name"
        ),
        "nice_1f005_info13" => array(
            "ph1", "ph2", "ph3", "job_name", "addr12", "addr22"
        ),
        "nice_1f005_info19" => array(
            "ph41", "ph42", "ph43"
        ),
        "nice_1f005_info29" => array(
            "ph1", "ph2", "addr12"
        ),
        "nice_1f005_info30" => array(
            "job_name", "addr22", "ph3"
        ),
        "nice_1f005_info38" => array(
            "change_ssn", "change_name"
        ),
        "nice_1f00d" => array(
            "ssn11", "ssn12", "ssn", "name"
        ),
        "nice_cert" => array(
            "ssn11", "ssn12", "ssn", "name"
        ),
        
        "nice_ews" => array(
            "name", "ssn"
        ),
    );
}