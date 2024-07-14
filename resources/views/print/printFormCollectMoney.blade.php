@extends('layouts.masterPop')
@section('content')
<?
$u_agent = $_SERVER['HTTP_USER_AGENT'];  //  들어온사람의 브라우저를 변수 $u_agent 에 담아서
$zoom_ratio = "85";

?>
<object id="factory" viewastext style="display:none" classid="clsid:1663ed61-23eb-11d2-b92f-008048fdd814" codebase="smsx.cab#Version=7.3.0.22"></object>
<form class="form-horizontal" name="trade_out_form" id="trade_out_form">
<table width="100%" border="0" cellspacing="1" cellpadding="1" id='header'>
<tr>
<td height="30" align=right style="padding-right:5px;filter:alpha(opacity=70)" bgcolor="#AAAAAA">
	<a href="javascript:printOk(<?//$zoom_ratio?>);" style="color:black"><i class="fas fa-print p-1 text text-xs"></i>인쇄하기</a>
</td>
</tr>
</table>
<br> 
<div style="zoom:80%">
<table cellSpacing=0 cellPadding=0  border=0 width=97% align=center>
<tr>
<td>

<table cellpadding=0 cellspacing=0 width="100%" border="0">
    <tr valign=bottom>
        <td align=center style="FONT-WEIGHT: bold; FONT-SIZE: 15pt; LETTER-SPACING: 55px; " >집 금 양 식</td>
    </tr>
</table>
    
<table width="100%"cellspacing=5 cellpadding=0 bgcolor="FFFFFF" align=center valign=top>
    <tr valign=bottom>
        <td><img src="/img/user.gif" align=absmiddle> 고객정보</td>
    </tr>
    <tr>
        <td>
       
            <table width="100%" height="100%" cellspacing=2 cellpadding=0 bgcolor="#CCCCCC" style="margin-bottom:5px">
                <tr>
                    <td bgcolor="FFFFFF">
                        <table width="100%" cellspacing=2 cellpadding=1>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                    
                            <tr height=23>
                                <td>차입자번호</td>
                                <td><font color='red'><?=$data['cust_info_no']?></font>&nbsp;&nbsp;<? //if($data[old_member_no] > 0 ) echo ",  구(".$data[old_member_no].")";?></td>
                                <td>계약번호</td>
                                <td><?=$data['loan_info_no']?></td>
                                <td>이름</td>
                                <td><?=$data['name']?></td>
                                <td>주민등록</td>
                                <td><?=Func::ssnFormat($data['ssn'])?></td>
                            </tr>
                            <tr height=23>
                                <td>상태</td>
                                <td><?=Vars::$arrayContractSta[$data['status']]?></td>
                                <td>계약유형</td>
                                <td><?=$data['loan_type']?></td>
                                <td>상환방법</td>
                                <td><?=$data['return_method_cd']?></td>
                                <td>상품</td>
                                <td><?=$data['pro_cd']?></td>
                            </tr>
                            <tr height=23>
                                <td>신청경로</td>
                                <td><?=$data['path_cd']?></td>
                                <td>제휴사</td>
                                <td><?//$data['com_code']?></td>
                                <td>계약일</td>
                                <td><?=$data['loan_date']?></td>
                                <td>만기일</td>
                                <td><?=$data['last_loan_date']?></td>
                            </tr>
                    <? if($data['promise_date']) { ?>
                            <tr height=23>
                                <td bgcolor="FFDDDD">약속일시</td>
                                <td><?=$data['promise_date']?> <?=$data['promise_hour']?>:<?=$data['promise_min']?></td>
                                <td bgcolor="FFDDDD">약속금액</td>
                                <td><?=Func::numberFormat($data['promise_money'])?>원</td>
                                <td bgcolor="FFDDDD">약속직원<?=$data['promise_id']?></td>
                                <td><?=$data['promise_id']?>, <?=$data['promise_save_time']?></td>
                                <td bgcolor="FFDDDD">약속실행</td>
                                <td><?//$data['promise_exec']?></td>
                            </tr>
                    <?}?>
                        </table>
                    </td>
                </tr>
            </table>
    
            <table width="100%" cellspacing=1 cellpadding=0 bgcolor="CCCCCC" style="margin-bottom:5px">
                <tr>
                    <td bgcolor="FFFFFF">
                        <table width="100%" cellspacing=2 cellpadding=1>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="17%" bgcolor="FFFFFF"></col>
        
                            <tr height=23>
                                <td>지점</td>
                                <td><?=$data['manager_code']?>
                                </td>
                                <td>담당</td>
                                <td><?=$data['manager_id']?></td>
                                <td>추가담당</td>
                                <td><?//$data['add_manager']]?></td>
                                <td><?//$att1_name?></td>
                                <!-- 채권구분1 -->
                                <td><?//$array_conf[att1][$data[attribute1_cat_no]]?></td>
                            </tr>
                        <!-- 채권구분 항목 : 채권구분2, NPL채권구분, 채권등급, 성향등급 -->
                            <tr height=23>
                                <td><?//$att2_name?></td>
                                <td><?//$array_conf[att2][$data[attribute2_cat_no]]?></td>
                                <td><?//$att3_name?></td>
                                <td><?//$array_conf[att3][$data[attribute3_cat_no]]?></td>
                                <td><?//$att4_name?></td>
                                <td><?//$array_conf[att4][$data[attribute4_cat_no]]?></td>
                                <td><?//$att5_name?></td>
                                <td><?//$array_conf[att5][$data[attribute5_cat_no]]?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
    
            <table width="100%" cellspacing=1 cellpadding=0 bgcolor="CCCCCC">
                <tr>
                    <td bgcolor="FFFFFF">
                        <table width="100%" cellspacing=2 cellpadding=1>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="42%" bgcolor="FFFFFF"></col>
                            <col width="8%" bgcolor="EEEEEE" align=center></col>
                            <col width="42%" bgcolor="FFFFFF"></col>

                            <tr height=23>
                                <td>집전화</td>
                                <td><?=Func::phFormat($data['ph11'], $data['ph12'], $data['ph13'])?> ( <?//$array_conf[call_flag][$data[ph1_call_flag]]?> )</td>
                                <td>직장명</td>
                                <td><?=$data['com_name']?> ( <?//$array_job_class[$data[job_class]]?> ) 
                                    <?//	if($data[retire]=="Y") echo "퇴사 ";?>
                                </td>
                            </tr>
    
                            <tr height=23>
                                <td>핸드폰</td>
                                <td><?=Func::phFormat($data['ph21'], $data['ph22'], $data['ph23'])?> ( <?//$array_conf[call_flag][$data[ph2_call_flag]]?> )</td>
                                <td>부서</td>
                                <td><?=$data['com_part']?>
                                    
                                    <?	if($data['com_grade']) echo "( 직급 : ".$data['com_grade']." )"; ?>
                            
                                </td>
                            </tr>
    
                            <tr height=23>
                                <td>안내전화</td>
                                <td><?=Func::phFormat($data['ph21'], $data['ph22'], $data['ph23'])?> 
                                <?//	if($data[ph9_name]) echo "( 명의 : ".$data[ph9_name]." )"; ?>
                                <?//	if($data[ph9_name_rel]) echo "( 직급 : ".$array_conf[relation][$data[ph9_name_rel]]." )"; ?>
                                </td>
                                <td>업종</td>
                                
                                <td><?=$data['job_cd']?> <?//$array_job[$job_code][$data[job_code]]?> <?//$array_conf[com_jikmu][$data[com_jikmu]]?></td>
                            </tr>
    
    
                            <tr height=23>
                                <td rowspan=3>등본주소</td>
                                <td rowspan=3>
                                    <table cellspacing=0 cellpadding=0>
                                        <tr>
                                            <td><?=$data['zip2']?></td>
                                            <td align=right>
                                                <?//	if($data[addr2_flag]) echo "( 확인여부 : ".$array_conf[addr_flag][$data[addr2_flag]]." )"; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan=2><?=$data['addr21']?><br><?=$data['addr22']?></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>입사년월</td>
                                <td>
                                <?
                                if($data['com_year'] && $data['com_months'])
                                {
                                    echo $data['com_year']."년 ".$data['com_months']."월";

                                    $com_term = (date("Y") - $data['com_year']) * 12 + date("n") - $data['com_months'];
                                    if($com_term >= 12)
                                    {
                                        $com_term = floor($com_term/12)."년 ".($com_term%12)."개월 차";
                                    }
                                    else
                                    {
                                        $com_term = $com_term."개월 차";
                                    }
                                ?>
                                입사 <?=$com_term?>
                                <?}?>
                                </td>
                            </tr>
                            <tr>
                                <td>직통전화</td>
                                <td>
                                    <?=$data['ph31']."-".$data['ph32']."-".$data['ph33']." 내선(".$data['ph34'].")"?> ( <?//$array_conf[call_flag][$data[ph3_call_flag]]?> )
                                </td>
                            </tr>
                            <tr>
                                <td>대표전화</td>
                                <td>
                                    <?//$data[ph61]."-".$data[ph62]."-".$data[ph63]?> ( <?//$array_conf[call_flag][$data[ph6_call_flag]]?> )
                                </td>
                            </tr>

                            <tr>
                                <td rowspan=3>실거주소</td>
                                <td rowspan=3>
                                    <table cellspacing=0 cellpadding=0>
                                        <tr>
                                            <td><?=$data['zip1']?></td>
                                            <td align=right>
                                            <?//	if($data[addr1_flag]) echo "( 확인여부 : ".$array_conf[addr_flag][$data[addr1_flag]]." )"; ?>
                                            </td>
                                         </tr>
                                        <tr>
                                            <td colspan=2><?=$data['addr11']?><br><?=$data['addr12']?></td>
                                        </tr>
                                        <?/*
                                        if(sizeof($array_conf['bad_local']))
                                        {
                                            foreach( $array_config['bad_local'] as $key => $value )
                                            {
                                                if(substr_count($data['addr11']." ".$data['addr12'], $value))
                                                {
                                                    echo "<tr><td colspan=2><font color=red>대출불가지역 : ".$value."</font></td></tr>";
                                                    break;
                                                }
                                            }
                                        }*/
                                        ?>
                                    </table>
                                </td>
                                <td>급여통장</td>
                                <td><?//$array_config['bank'][$data[com_bank_cat_no]]  ?? ''?>	<?=$data['com_ssn']?>
                                </td>
                            </tr>
                            <tr>
                                <td>월수입</td>
                                <td><?//$data[com_pay]?>만원, 매월 <?=$data['pay_day']?>일
                                </td>
                            </tr>
                            <tr>
                                <td>고용형태</td>
                                <td><? if(!empty($data['com_employ_cd'])) { ?>
                                            <?=$array_config['employ_cd'][$data['com_employ_cd']]  ?? ''?>
                                    <?}?>

                                </td>
                            </tr>

                            <tr>
                                <td>우편물주소</td>
                                <td>
                                    <table cellspacing=0 cellpadding=0>
                                        <tr>
                                            <td>
                                                <? if(!empty($data['post_send_cd'])) { ?>
                                                <?=$data['zip'.$data['post_send_cd']]?>
                                                <?}?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <? if(!empty($data['post_send_cd'])) { ?>
                                                    <?=$data['addr'.$data['post_send_cd'].'1']?><br><?=$data['addr'.$data['post_send_cd'].'2']?>
                                                <?}?>
                                            </td>
                                        </tr>
                                        <?/*
                                        if(sizeof($array_config['bad_local']))
                                        {
                                            foreach( $array_config['bad_local'] as $key => $value )
                                            {
                                                if(substr_count($data['addr'.$data['post_send_cd'].'1']." ".$data['addr'.$data['post_send_cd'].'2'], $value))
                                                {
                                                    echo "<tr><td colspan=2><font color=red>대출불가지역 : ".$value."</font></td></tr>";
                                                    break;
                                                }
                                            }
                                        }*/
                                        ?>
                                    </table>
                                </td>
                                <td>직장주소</td>
                                <td>
                                    <table cellspacing=0 cellpadding=0>
                                        <tr>
                                            <td><?=$data['zip3']?></td>
                                            <td align=right>
                                                <?//	if($data['addr3_flag']) echo "( 확인여부 : ".$array_confing['addr_flag'][$data['addr3_flag']]." )"; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan=2><?=$data['addr31']?><br><?=$data['addr32']?></td>
                                        </tr>
                                        <?/*
                                        if(sizeof($array_confing['bad_local']))
                                        {
                                            foreach( $array_confing['bad_local'] as $key => $value )
                                            {
                                                if(substr_count($data['addr31']." ".$data['addr32'], $value))
                                                {
                                                    echo "<tr><td colspan=2><font color=red>대출불가지역 : ".$value."</font></td></tr>";
                                                    break;
                                                }
                                            }
                                        }*/
                                        ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>채불등록</td>
                                <td>
                                    통지일 : <?=$data['bad_post_date']?> &nbsp; &nbsp;
                                    등록일 : <?=$data['bad_reg_date']?>
                                    
                                </td>
                                <td>월수입</td>
                                <td>
                                    <?//$data['com_pay']?>만원, 매월<?=$data['pay_day']?> 일
                                </td>
                            </tr>
                            <tr height=23>
                                <td>말소</td>
                                <td>
                                <?/*
                                    if($data['cancel_chk']=="Y")
                                    {
                                        echo "말소 ";
                                        echo "( ".$data['cancel_chk']." )";
                                    }*/
                                ?>

                                <!-- 기존 전산에서도 사용 안하는 컬럼 : bad_reason_date -->
                                <?//( $data[bad_reason_date]<=date("Y-m-d") && $data['balance']>0 ) ? "　- 불량사유발생일: ".$data[bad_reason_date] : "" ; ?>
                                </td>
                                <td>사업자번호</td>
                                <td><?=substr($data['com_ssn'], 0, 3).'-'.substr($data['com_ssn'], 3, 2).'-'.substr($data['com_ssn'], 6)?></td>
                            </tr>

                            <tr>
                                <td>회수메모</td>
                                <td><?=$data['memo']?></td>
                                <td>소득메모</td>
                                <td><?//$data['com_memo']?></td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>

        </td>

<br>
    <? if(!empty($data['guarantor'])) {?>
    <? foreach($data['guarantor'] as $guarantor => $vg){ ?>
        <tr valign=bottom>
            <td><img src="/img/user.gif" align=absmiddle> 보증인정보</td>
        </tr>
        <tr>
            <td>
                <table width="100%" height="100%" cellspacing=2 cellpadding=0 bgcolor="#CCCCCC" style="margin-bottom:5px">
                    <tr>
                        <td bgcolor="FFFFFF">
                            <table width="100%" cellspacing=2 cellpadding=1>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="17%" bgcolor="FFFFFF"></col>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="17%" bgcolor="FFFFFF"></col>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="17%" bgcolor="FFFFFF"></col>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="17%" bgcolor="FFFFFF"></col>
                                <tr height=23>
                                    <td>이름</td>
                                    <td><?=$vg['name']?></td>
                                    <td>주민등록</td>
                                    <td><?=Func::ssnFormat($vg['ssn'], '-')?></td>
                                    <td>동거</td>
                                    <td><?//$vg['family_living']?></td>
                                    <td>관계</td>
                                    <td><?=$array_config['relation_cd'][$vg['relation_cd']] ?? ''?></td>
                                </tr>

                                <? if(isset($vg['promise_date'])) { ?>
                                <tr height=23>
                                    <td bgcolor="FFDDDD">약속일시</td>
                                    <td><?//$vg['promise_date']?> <?//$vg['promise_hour']?>:<?//$vg['promise_min']?></td>
                                    <td bgcolor="FFDDDD">약속금액</td>
                                    <td><?//Func::numberFormat($vg['promise_money'])?>원</td>
                                    <td bgcolor="FFDDDD">약속직원<?//$vg['promise_id']?></td>
                                    <td><?//$array_total_member[$data['promise_id']] ?? '' ?>, <?//date("m-d H:i", $vg['promise_save_time'])?></td>
                                    <td bgcolor="FFDDDD">약속실행</td>
                                    <td><?//$vg['promise_exec']?></td>
                                </tr>
                                <?}?>
                            </table>
                        </td>
                    </tr>
                </table>

                <table width="100%" cellspacing=1 cellpadding=0 bgcolor="CCCCCC">
                    <tr>
                        <td bgcolor="FFFFFF">
                            <table width="100%" cellspacing=2 cellpadding=1>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="42%" bgcolor="FFFFFF"></col>
                                <col width="8%" bgcolor="EEEEEE" align=center></col>
                                <col width="42%" bgcolor="FFFFFF"></col>
                                <tr height=23>
                                    <td>집전화</td>
                                    <td><?=Func::phFormat($vg['ph11'], $vg['ph12'], $vg['ph13'])?> ( <?//$array_config['call_flag'][$vg['ph1_call_flag']]?> )</td>
                                    <td>직장명</td>
                                    <td><?=$vg['com_name']?> ( <?//$array_job_class[$vg['job_class']]?> ) 
                                    <?//	if($vg['retire']=="Y") echo "퇴사 ";?>
                                    </td>
                                </tr>
                                <tr height=23>
                                    <td>핸드폰</td>
                                    <td><?=Func::phFormat($vg['ph21'], $vg['ph22'], $vg['ph23'])?> ( <?//$array_config['call_flag'][$vg['ph2_call_flag']]?> )</td>
                                    <td>부서</td>
                                    <td><?=$vg['com_part']?>
                                    <?//	if($vg['com_grade']) echo "( 직급 : ".$array_config['member_class'][$vg['com_grade']]." )"; ?>
                                    </td>
                                </tr>

                                <tr height=23>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>업종</td>
                                    <td><?//$array_job['name'][$job_cd]?> <?//$array_job[$job_code][$data['job_cd']]?> <?//$array_config['com_jikmu'][$vg['com_jikmu']]?></td>
                                </tr>

                                <tr height=23>
                                    <td rowspan=3>등본주소</td>
                                    <td rowspan=3>
                                        <table cellspacing=0 cellpadding=0>
                                            <tr>
                                                <td><?=$vg['zip2']?>
                                                </td>
                                                <td align=right>
                                                <?//	if($vg['addr2_flag']) echo "( 확인여부 : ".$array_config['addr_flag'][$vg['addr2_flag']]." )"; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><?=$vg['addr21']?><br><?=$vg['addr22']?></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>입사년월</td>
                                    <td>입사</td>
                                </tr>
                                <tr>
                                    <td>직통전화</td>
                                    <td>
                                    <?=Func::phFormat($vg['ph31'], $vg['ph32'], $vg['ph33'])." 내선(".$vg['ph34'].")"?> ( <?//$array_config['call_flag'][$vg['ph3_call_flag']]?> )
                                    </td>
                                </tr>
                                <tr>
                                    <td>대표전화</td>
                                    <td><?//Func::phFormat($vg['ph61'], $vg['ph62'], $vg['ph63']) ?> ( <?//$array_config['call_flag'][$vg['ph6_call_flag']]?> )</td>
                                </tr>
                                <tr>
                                    <td rowspan=3>실거주소</td>
                                    <td rowspan=3>
                                        <table cellspacing=0 cellpadding=0>
                                            <tr>
                                                <td><?=$vg['zip1']?></td>
                                                <td align=right>
                                                    <?//	if($vg['addr1_flag']) echo "( 확인여부 : ".$array_config['addr_flag'][$vg['addr1_flag']]." )"; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan=2><?=$vg['addr11']?><br><?=$vg['addr12']?></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>급여통장</td>
                                    <td><?//$array_conf[bank][$vg['com_bank_cat_no']]?>	<?=$vg['com_ssn']?></td>
                                </tr>
                                <tr>
                                    <td>월수입</td>
                                    <td><?//$vg['com_pay']?>만원, 매월 <?=$vg['pay_day']?>일</td>
                                </tr>
                                <tr>
                                    <td>고용형태</td>
                                    <td><?//$array_config['employ_cd'][$vg['com_employ_cd']]?></td>
                                </tr>
                                <tr>
                                    <td>우편물주소</td>
                                    <td>
                                        <table cellspacing=0 cellpadding=0>
                                            <tr>
                                                <td>
                                                <? if(!empty($vg['post_send_cd'])){ ?>
                                                    <?=$vg['zip'.$vg['post_send_cd']]?> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                    <?}?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <? if(!empty($vg['post_send_cd'])){ ?>
                                                    <?=$vg['addr'.$vg['post_send_cd'].'1']?><br><?=$vg['addr'.$vg['post_send_cd'].'2']?>
                                                    <?}?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>직장주소</td>
                                    <td>
                                        <table cellspacing=0 cellpadding=0>
                                            <tr>
                                                <td><?=$vg['zip3']?></td>
                                                <td align=right><?//	if($vg['addr3_flag']) echo "( 확인여부 : ".$array_config['addr_flag'][$vg['addr3_flag']]." )"; ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan=2>
                                                <?=$vg['addr31']?><br><?=$vg['addr32']?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td>채불등록</td>
                                    <td>
                                    통지일 : <?//$vg['bad_post_date']?> &nbsp; &nbsp;
                                    등록일 : <?//$vg['bad_reg_date']?>
                                    </td>
                                    <td>월수입</td>
                                    <td>
                                    <?//$vg['com_pay']?>만원, 매월<?=$vg['pay_day']?> 일
                                    </td>
                                </tr>
                                <tr height=23>
                                    <td>말소</td>
                                    <td>
                                    <!-- 기존 전산에서도 사용 안하는 컬럼 : bad_reason_date -->
                                    <!-- <?//( $vg['bad_reason_date']<=date("Y-m-d") && $vg['balance']>0 ) ? "　- 불량사유발생일: ".$vg['bad_reason_date'] : "" ; ?> -->
                                    </td>
                                    <td>사업자번호</td>
                                    <td>
                                    <?=substr($data['com_ssn'], 0, 3).'-'.substr($data['com_ssn'], 3, 2).'-'.substr($data['com_ssn'], 6)?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>회수메모</td>
                                    <td><?//$vg['memo']?></td>
                                    <td>소득메모</td>
                                    <td><?//$vg['com_memo']?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    <?}?>
    <?}?>
</table>

<table cellpadding=0 cellspacing=0 width="100%">
    <tr valign=bottom>
        <td><img src="/img/user3.gif" align=absmiddle> 계약정보 </td>
    </tr>
</table>
<table width="100%" cellspacing=2 cellpadding=0 bgcolor="#CCCCCC" style="margin-bottom:5px">
    <tr>
        <td bgcolor="FFFFFF">
            <table width="100%" cellspacing=2 cellpadding=1>
                <col width="9%" bgcolor="EEEEEE" align=center></col>
                <col width="16%" bgcolor="FFFFFF"></col>
                <col width="9%" bgcolor="EEEEEE" align=center></col>
                <col width="16%" bgcolor="FFFFFF"></col>
                <col width="9%" bgcolor="EEEEEE" align=center></col>
                <col width="16%" bgcolor="FFFFFF"></col>
                <col width="9%" bgcolor="EEEEEE" align=center></col>
                <col width="16%" bgcolor="FFFFFF"></col>

                <tr height=25>
                    <td>계약일</td>
                    <td><?=$data['loan_date']?></td>
                    <td>최초대출</td>
                    <td><?=Func::numberFormat($data['first_loan_money'])?>원</td>
                    <td>최근대출</td>
                    <td><?//$array_out_sub_type[$data['last_out_type']]?></td>
                    <td>최근대출일</td>
                    <td><?//$data['last_out_date']?></td>
                </tr>

                <tr height=25>
                    <td>약정일</td>
                    <td><?=$data['contract_day']?>일</td>
                    <td>이율</td>
                    <td><?//$data['ratio']?> / <?//$data['delay_ratio']?></td>
                    <td>한도액</td>
                    <td><?=Func::numberFormat($data['limit_money'])?>원</td>
                    <td>한도설정일</td>
                    <td><?//$data['limit_money_date']?></td>
                </tr>

                <tr height=25>
                    <td>마지막거래</td>
                    <td style="color:blue"><?=$data['last_trade_date']?></td>
                    <td>상환일</td>
                    <td><?=$data['return_date']?></td>
                    <td>상환일이자</td>
                    <td><?=Func::numberFormat($data['return_date_interest'])?>원</td>
                    <td bgcolor="EEAAEE">여신가능액</td>
                    <td bgcolor="F3D9F3"><?=Func::numberFormat($data['limit_money'] - $data['balance'])?>원</td>
                </tr>

                <tr height=25>
                    <td>연체일</td>
                    <td style="color:red"><?=$data['delay_term']?>일 (<?=$data['delay_interest_term']?>일) </td>
                    <td>최대연체일</td>
                    <td><?=$data['delay_term_max']?>일</td>
                    <td>누적연체일</td>
                    <td><?=$data['delay_term_sum']?>일</td>
                    <td rowspan=2>가상계좌</td>
                    <td rowspan=2 style="color:blue">
                        <?//get_virtual_account($data['cust_list_no'], $contract_info_no)?>
                </td>
                </tr>
                <tr height=25>
                    <td>부족금</td>
                    <td style="color:red"><?=Func::numberFormat($data['lack_delay_money'])?>원</td>
                    <td>당일청구금액</td>
                    <td style="color:red"><?=Func::numberFormat($data['charge_money'])?>원</td>
                    <td>월상환액</td>
                    <td style="color:blue"><?=Func::numberFormat($data['monthly_return_money'])?>원</td>
                </tr>

                <tr height=25>
                    <td>미수금</td>
                    <td><?=Func::numberFormat($data['misu_money'])?>원</td>
                    <td>연체이자</td>
                    <td><?=Func::numberFormat($data['delay_interest'])?>원</td>
                    <td>정상이자</td>
                    <td><?=Func::numberFormat($data['interest'])?>원</td>
                    <td>화해이자</td>
                    <td><?=Func::numberFormat($data['settle_interest'])?>원</td>
                </tr>

                <tr height=25>
                    <td>비용</td>
                    <td><?//Func::numberFormat($data['pre_money'])?>원</td>
                    <td bgcolor="EEBBBB">이자합계</td>
                    <td bgcolor="FFEEEE"><?=Func::numberFormat($data['interest_sum'])?>원</td>
                    <td bgcolor="BBBBEE">잔액</td>
                    <td bgcolor="EEEEFF"><?=Func::numberFormat($data['balance'])?>원</td>
                    <td bgcolor="BBEEBB">완납금액</td>
                    <td bgcolor="EEFFEE"><?=Func::numberFormat($data['balance'] + $data['interest_sum'])?>원</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
        
<br style="page-break-before:always;" />

<table cellpadding=0 cellspacing=0 width="100%" valign=top>
    <tr valign=bottom>
        <td><img src="/img/user3.gif" align=absmiddle> 중요메모</td>
    </tr>
</table>

<table width="100%" height="" cellspacing=1 cellpadding=1 bgcolor="999999" style="margin-top:3px;margin-bottom:5px">
    <col width="10%">
    <col width="10%">
    <col width="80%">

    <tr height=20 bgcolor="EEEEEE" align=center>
        <td>작성자</td>
        <td>작성일자</td>
        <td>내용</td>
    </tr>

    <tr bgcolor="FFFFFF">
        <td><?//$array_total_member[$data['imemo_worker_id']]?></td>
        <td>
        <?
            //if($data['imemo_save_time']) echo date("Y-m-d H:i:s", $data['imemo_save_time']);
            //else echo "";
        ?>
        </td>
        <td><?//$data['important_memo']?></td>
        <td></td>
    </tr>
</table>
    
<table cellpadding=0 cellspacing=0 width="100%" valign=top>
    <tr valign=bottom>
        <td><img src="/img/user3.gif" align=absmiddle> 메모</td>
    </tr>
</table>
<table width="100%" height="" cellspacing=1 cellpadding=1 bgcolor="999999" style="margin-top:3px;margin-bottom:5px">
    <col width=5%>
    <col width=5%>
    <col width=6%>
    <col width=10%>
    <col width=8%>
    <col width=8%>
    <col width=8%>
    <col width=55%>

    <tr height=20 bgcolor="EEEEEE" align=center>
        <td>구분</td>
        <td>대상</td>
        <td>입력자</td>
        <td>입력시간</td>
        <td>약속일</td>
        <td>약속시간</td>
        <td>약속금액</td>
        <td>메모</td>
    </tr>
    
    <!-- 메모 여러개 -->
    <? foreach($data['memos'] as $memo => $v_m){ ?>
    <tr height=20 align=center onclick="input('<?=$v_m['no']?>');" style="cursor:hand" bgcolor="FFFFFF">
        <td></td>
        <td><?=$array_config['relation_cd'][$v_m['relation_cd']] ?? ''?></td>
        <td><?=$array_total_member[$v_m['save_id']] ?? ''?></td>
        <td><?=Func::dateFormat($v_m['save_time'], '-')?></td>
        <td><?=$v_m['promise_date']?></td>
        <td><?=$v_m['promise_hour']?></td>
        <td><?=Func::numberFormat($v_m['promise_money'])?></td>
        <td><?=$v_m['memo']?></td>
    </tr>
    <?}?>
</table>

<br style="page-break-before:always;" />

<table cellpadding=0 cellspacing=0 width="100%" valign=top>
    <tr valign=bottom>
        <td><img src="/img/user3.gif" align=absmiddle> 예상이자 </td>
    </tr>
</table>
<table width="100%" height=""  cellspacing=1 cellpadding=1 bgcolor="666666" style="margin-top:3px;margin-bottom:5px;" id="T<?=$data['loan_info_no']?>">
    <tr bgcolor="FFFFFF">
    <col align=center></col>
    <col align=center></col>
    <col align=center></col>
    <col width="80" align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=right style="padding-right:2px"></col>
    <col align=center></col>
    <col align=center></col>
    <col align=center></col>

    <tr bgcolor="EEEEEE" height=20 align=center>
        <td>상태</td>
        <td>기준일</td>
        <td>경과일수</td>
        <td align=center>당일청구금액</td>
        <td align=center>법비용</td>
        <td align=center>총이자</td>
        <td align=center>정상이자<br>연체이자</td>
        <td align=center>부족금<br>미수금</td>
        <td align=center>잔액<br>화해이자</td>
        <td align=center>완납금액</td>
        <td>이율</td>
        <td>상환일</td>
    </tr>

    <tr align=center bgcolor="FFFFFF">
        <td><?=($data['delay_interest_term'])? "<font color=red>연</font>" : "<font color=blue>정</font>"; ?></td>
        <td><?//$today?></td>
        <td><?//$pass_day?><br><?=$data['interest_term']?>/<?=$data['delay_interest_term']?></td>
        <td><?=Func::numberFormat($data['charge_money'])?></td>
        <td><?//Func::numberFormat($data['pre_money'])?></td>
        <td><?=Func::numberFormat($data['interest_sum'])?></td>
        <td>
            <?=Func::numberFormat($data['interest'])?><br>
            <?=Func::numberFormat($data['delay_interest'])?>
        </td>
        <td>
            <?=Func::numberFormat($data['lack_delay_money'])?><br>
            <?=Func::numberFormat($data['misu_money'])?></td>
        <td>
            <?=Func::numberFormat($data['balance'])?><br>
            <?=Func::numberFormat($data['settle_interest'])?>
        </td>
        <td><?//Func::numberFormat($data['pre_money'] + $data['interest_sum'] + $data['balance'])?></td>
        <td><?=$data['loan_rate']?>~<?=$data['loan_delay_rate']?></td>
        <td><?=Func::dateFormat($data['return_date'])?></td>
    </tr>
</table>

<br style="page-break-before:always;" />

<table cellpadding=0 cellspacing=0 width="100%" valign=top border="0">
    <tr valign=bottom>
        <td><img src="/img/user3.gif" align=absmiddle> 거래원장 </td>
    </tr>
</table>

<table width="100%" height="100%" cellspacing=1 cellpadding=1 bgcolor="666666" style="margin-top:3px;margin-bottom:10px;" id="T<?//$contract_info_no?>">
	<tr bgcolor="EEEEEE" height=20 align=center>
        <td></td>
        <td>형식<br>경로</td>
        <td>거래일</td>
        <td>이율</td>
        <td>경과</td>
        <td>정상이자<br>연체이자</td>
        <td>입출금액</td>
        <td>비용상환<br>비용감면</td>
        <td>이자상환<br>이자감면</td>
        <td>원금상환<br>원금감면</td>
        <td>잔액<br>화해이자</td>
        <td>부족금<br>미수금</td>
        <td>비용<br>가수금</td>
        <td>다음상환일<br>월상환부족</td>
	</tr>
    
    <?
    if(!empty($data['trade'])) { 
        foreach($data['trade'] as $num => $vt) {
    ?>
    <? if($vt->trade_div == 'O'){ ?>
    <tr <?=Func::trColor($data['out']['bgcolor2'], $data['out']['bgcolor1'])?> height=20 align=center style="display:<?=$data['tr_display']?>" status="<?=$data['save_status']?>">
    <? } else { ?>
    <tr <?=Func::trColor($data['in']['bgcolor2'], $data['in']['bgcolor1'])?> height=20 align=center style="display:<?=$data['tr_display']?>" status="<?=$data['save_status']?>">
    <?}?>
        <td><?=Vars::$arrayTradeDiv[$vt->trade_div]?><?=$data['del_title']?></td>
        <? if($vt->trade_div == "I") {?>
        <td align=center><?=$tradeInType[$vt->trade_type]?? ''?><br><?=$tradeInPath[$vt->trade_path_cd]?? ''?></td>
        <?} else if($vt->trade_div == "O") {?>
        <td align=center><?=$tradeOutType[$vt->trade_type]?? ''?><br><?$tradeOutPath[$vt->trade_path_cd]?? ''?></td>
        <?}?>
        <td><?=$data['strike']?><?=Func::dateFormat(substr($vt->trade_date,2), '-')?><br><?=$data['delay_text']?></td>
        <td><?//=$vt->ratio?><br><?//=$vt->delay_ratio']?></td>
        <td><?=$vt->interest_term?>/<?=$vt->delay_interest_term?><br><?=$vt->delay_term?></td>
        <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->interest)?><br><?=Func::numberFormat($vt->delay_interest)?></td>
        <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->trade_money)?></td>
        <td align=right><?=$data['strike']?><?//Func::numberFormat($vt->return_pre_money?><br><?//Func::numberFormat($vt->lose_pre_money)?></td>
        <td align=right><?=$data['strike']?><a href="#" title="<?=$return_interest_title?>"><?=Func::numberFormat($vt->return_interest_sum)?></a><br><?=Func::numberFormat($vt->lose_interest)?></td>
        <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->return_origin)?><br><?=Func::numberFormat($vt->lose_origin)?></td>
        <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->balance)?><br><?=Func::numberFormat($vt->settle_interest)?></td>
        <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->lack_delay_money)?><br><?=Func::numberFormat($vt->misu_money)?></td>
        <td align=right><?=$data['strike']?><?//Func::numberFormat($vt->pre_money)?><br><?=Func::numberFormat($vt->over_money)?></td>
        <td><?=$data['strike']?><?=Func::dateFormat(substr($vt->return_date,2), '-')?><br><?=Func::numberFormat($vt->lack_delay_money)?></td>
    </tr>

    <?
        }
    }
    ?>

    <!-- 화해일 때 -->
    <!-- <tr align=center>
        <td colspan=20><?//$strike?>총 <?//Func::numberFormat($vs[settle_money])?>원, <?//$vs[settle_cnt]?> 분할 화해 시작</td>
    </tr> -->

    <tr bgcolor="EEEEEE" height=20 align=center>
        <td colspan=2>상환합계</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align=right><?//Func::numberFormat($sum[return_pre_money]+$sum[return_interest_sum]+$sum[return_origin])?></td>
        <td align=right><?//Func::numberFormat($sum[return_pre_money])?><br><?//Func::numberFormat($sum[lose_pre_money])?></td>
        <td align=right><?//Func::numberFormat($sum[return_interest_sum])?><br><?//Func::numberFormat($sum[lose_interest])?></td>
        <td align=right><?//Func::numberFormat($sum[return_origin])?><br><?//Func::numberFormat($sum[lose_origin])?></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>   

    <!-- 화해일 때 -->
    <tr bgcolor=CCCCCC height=25>
        <!-- <td class="ltbtd" rowspan=<?//sizeof($array_settle_plan)+1?> colspan=2 align=center>화해스케줄</td> -->
        <td class="ltbtd" rowspan=2 colspan=2 align=center>화해스케줄</td>
        <td align=center><?//$s_trade_date?></td>
        <td align=center></td>
        <td align=center><?//$vs[cnt]?>회</td>
        <td align=right><?//Func::numberFormat($vs[money])?></td>
        <td align=right><?//Func::numberFormat($vs[in_money])?></td>
        <td align=center colspan=3><?//($vs[money]==$vs[in_money]) ? "<font color=''>完</font>" : "" ; ?></td>
        <td align=right><?//Func::numberFormat($vs[money]-$vs[in_money])?></td>
        <td align=center colspan=9></td>
    </tr>

    <tr bgcolor="666666" height=25 style="color:white">
        <td align=center>합계</td>
        <td align=center></td>
        <td align=center></td>
        <td align=right><?//Func::numberFormat($total_settle_money)?></td>
        <td align=right><?//Func::numberFormat($return_settle_money)?></td>
        <td align=center colspan=3></td>
        <td align=right><?//Func::numberFormat($remain_settle_money)?></td>
        <!-- <td align=center colspan=9>실행율 : <?//percent_report($return_settle_money, $total_settle_money)?></td> -->
        <td align=center colspan=9>실행율 : %</td>
    </tr>

</table>

</td>
</tr>
</table>
</div>

</form>
<iframe name="printFrm" frameborder=0 style="display:none;"></iframe>
@endsection

@section('javascript')

<!--
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
-->
<style>
  #header { position:absolute; top:0px; z-index:99; }
</style>
<script>
// 메뉴 고정 스크립트
function scrollPage()
{
  document.all.header.style.pixelTop = document.body.scrollTop;
}

function printOk(p)
{
  var form_zoom = p;
  var agt = navigator.userAgent.toLowerCase();
  if( agt.indexOf("chrome") != -1 ) {
      //var popupwindow = window.open("","PRINT","height=1500, width=1000");
      printFrm.document.write("<style type='text/css' media='print'>"+
                                  "@page {"+
                                      "size: auto;  /* auto is the initial value */"+
                                      "margin: 0mm 0mm 0mm 0mm;  /* this affects the margin in the printer settings */"+
                                  "}"+
                                  "body { "+
                                    "zoom: "+form_zoom+"%; "+		//	배율 조정
                                    "background-color: red; "+
                                  "}"+
                                "</style>");
      var $printarea = $('form');
      
      if( $printarea.html().indexOf("<html>") != -1 ){
        printFrm.document.write( $printarea.html() );
        printFrm.document.getElementById('header').display="none";
        printFrm.document.all.header.style.display = "none";
      }else {
        printFrm.document.write('<body>');
        printFrm.document.write( $printarea.html() );
        printFrm.document.write('</body>');
        printFrm.document.getElementById('header').display="none";
        printFrm.document.all.header.style.display = "none";
      }

      printFrm.document.close();
      printFrm.focus();
      printFrm.print();
      printFrm.close();
  }else{
    document.getElementById('header').display="none";
    document.all.header.style.display = "none";
    factory.printing.Print(false, window);
    document.getElementById('header').display="";
    document.all.header.style.display = "";
  }
}
/*
function printSet()
{
  factory.printing.header = "";
  factory.printing.footer = "";
  factory.printing.leftMargin = 0;
  factory.printing.rightMargin = 0;
  factory.printing.topMargin = 0;
  factory.printing.bottomMargin = 0;
}
*/
$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');


// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        searchLoanInfo();
      };
    });
}
enterClear();
</script>

<script>  window.setInterval('scrollPage()',1); /*printSet(); */</script>
@endsection
