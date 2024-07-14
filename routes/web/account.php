<?php

// 투자관리

// 투자자정보창
Route::get('/account/investor',                  'Account\InvestorController@investor')	                    ->name('투자자정보 메인');
Route::post('/account/investorlist',             'Account\InvestorController@investorList')	                ->name('투자자정보 리스트');
Route::post('/account/investoraction',           'Account\InvestorController@investorAction')	            ->name('투자자정보 저장');
Route::post('/account/investordelete',           'Account\InvestorController@investorDelete')	            ->name('투자자정보 일괄삭제');
Route::post('/account/investorexcel',            'Account\InvestorController@investorExcel')			    ->name('투자자정보 엑셀다운로드');
Route::get('/account/investorpop',               'Account\InvestorController@investorPop')			        ->name('투자자정보 팝업창');

Route::post('/account/investorinfo',                                'Account\InvestorController@investorInfo')                 ->name('투자자관리 투자자정보');
Route::post('/account/investorinfoimage',                           'Account\InvestorController@investorInfoImage')            ->name('투자자관리 이미지메인');
Route::post('/account/investorinfoimageaction',                     'Account\InvestorController@investorInfoImageAction')      ->name('투자자관리 이미지저장');
Route::post('/account/investorinfosms',                             'Account\InvestorController@investorInfoSms')              ->name('투자자관리 문자메인');
Route::post('/account/investorinfosmsaction',                       'Account\InvestorController@investorInfoSmsAction')        ->name('투자자관리 문자저장');
Route::post('/account/investorinfomemo',                            'Account\InvestorController@investorInfoMemo')             ->name('투자자관리 메모메인');
Route::post('/account/investorinfomemoaction',                      'Account\InvestorController@investorInfoMemoAction')       ->name('투자자관리 메모저장');
Route::get ('/account/getinvestorinfoimg/{no}/{loan_usr_info_no}',  'Account\InvestorController@getInvestorInfoImg')           ->name('투자자관리 이미지가져오기');
Route::get ('/account/downinvestorimg/{no}',                        'Account\InvestorController@downInvestorImg')              ->name('투자자관리 이미지다운로드 링크');
Route::get ('/account/usrimagepriview/{no}/{loan_usr_info_no}/{ext}','Account\InvestorController@usrImagePriview')              ->name('PDF 이미지 크게보기');
Route::post('/account/investorinfochange',                          'Account\InvestorController@investorInfoChange')           ->name('투자자관리 변경내역메인');
Route::post('/account/investorinfodetail',                          'Account\InvestorController@investorInfoDetail')           ->name('투자자관리 투자내역메인');
Route::post('/account/investorinfodetaillist',                      'Account\InvestorController@investorInfoDetailList')       ->name('투자자관리 투자내역리스트');
Route::post('/account/investorinfodetailexcel',                     'Account\InvestorController@investorInfoDetailExcel')      ->name('투자자관리 투자내역 수익분배전체 엑셀다운로드');
Route::post('/account/investorpaymentexcel',                        'Account\InvestorController@investorPaymentExcel')         ->name('투자자관리 투자내역 지급내역 엑셀다운로드');
Route::post('/account/investortotalscheduleexcel',                  'Account\InvestorController@investorTotalScheduleExcel')   ->name('투자자관리 투자내역 전체스케줄 엑셀다운로드');
Route::post('/account/investorinputform',                           'Account\InvestorController@investorInputForm')            ->name('투자자 입력');

// 투자정보창
Route::get ('/account/investment',                'Account\InvestmentController@investment')                 ->name('투자계약관리 메인');
Route::post('/account/investmentlist',            'Account\InvestmentController@investmentList')             ->name('투자계약관리 리스트');
Route::get('/account/investmentform',             'Account\InvestmentController@investmentForm')             ->name('투자계약관리 입력 form');
Route::post('/account/investmentlumpdelete',      'Account\InvestmentController@investmentLumpDelete')       ->name('투자계약관리 일괄삭제');

Route::get ('/account/investmentpop',             'Account\InvestmentController@investmentPop')              ->name('상품내역관리 팝업창');
Route::post('/account/investmentinfo',            'Account\InvestmentController@investmentInfo')             ->name('상품내역관리 고객정보');
Route::post('/account/investmentinfolist',        'Account\InvestmentController@investmentinfoList')         ->name('상품내역관리 리스트');
Route::post('/account/investmentinfoaction',      'Account\InvestmentController@investmentInfoAction')       ->name('상품내역관리 저장');

Route::post('/account/reviewinvestschedule',      'Account\InvestmentController@reviewInvestSchedule')       ->name('이자분배스케줄미리보기');

Route::get('/account/exceluploadsample',          'Account\InvestmentController@excelUploadSample')	         ->name('투자스케줄 엑셀업로드샘플파일다운로드');
Route::post('/account/exceluploadform',           'Account\InvestmentController@excelUploadForm')            ->name('투자스케줄 엑셀업로드');
Route::post('/account/exceluploadformaction',     'Account\InvestmentController@excelUploadFormAction')      ->name('투자스케줄 엑셀업로드액션');

Route::post('/account/investmentimage',           'Account\InvestmentController@investmentImage')            ->name('투자리스트창 첨부파일');
Route::post('/account/investmentimagelist',       'Account\InvestmentController@investmentImageList')        ->name('투자리스트창 첨부파일리스트');
Route::post('/account/investmentimagedetail',     'Account\InvestmentController@investmentImageDetail')      ->name('투자리스트창 첨부파일상세');
Route::post('/account/investmentimageinput',      'Account\InvestmentController@investmentImageInput')       ->name('투자리스트창 첨부파일입력창');
Route::post('/account/investmenttransfer',        'Account\InvestmentController@investmentTransfer')         ->name('투자리스트창 양도/양수');
Route::post('/account/investmenttransferlist',    'Account\InvestmentController@investmentTransferList')     ->name('투자리스트창 양도/양수 list');
Route::post('/account/investmentimageaction',     'Account\InvestmentController@investmentImageAction')      ->name('고객정보창 파일저장');

Route::post('/account/investmentsms',             'Account\InvestmentController@investmentSms')              ->name('투자리스트창 문자');
Route::post('/account/investmentsmsview',         'Account\InvestmentController@investmentSmsView')          ->name('투자리스트창 문자상세');

Route::post('/account/investmentmemo',             'Account\InvestmentController@investmentMemo')            ->name('투자리스트창 메모');
Route::post('/account/investmentmemoview',         'Account\InvestmentController@investmentMemoView')        ->name('투자리스트창 메모상세');

Route::post('/account/investmemoinput',           'Account\InvestmentController@investMemoInput')            ->name('투자리스트창 메모입력창');
Route::post('/account/investmemoaction',          'Account\InvestmentController@investMemoAction')           ->name('투자리스트창 메모저장');

Route::post('/account/setinvestmentinfoschedule', 'Account\InvestmentController@setinvestmentInfoScheduleAction')->name('상품내역관리 고객정보 저장');
Route::post('/account/investmentexcel',           'Account\InvestmentController@investmentExcel')            ->name('상품내역관리 엑셀다운로드');
Route::post('/account/investmentchgratio',        'Account\InvestmentController@investmentChgRatio')         ->name('수수료변경');
Route::post('/account/investmentchgratiolist',    'Account\InvestmentController@investmentChgRatioList')     ->name('수수료변경 list');
Route::post('/account/investmentchgratioaction',  'Account\InvestmentController@investmentChgRatioAction')   ->name('수수료변경 action');
Route::post('/account/investmentpaper',           'Account\InvestmentController@investmentPaper')            ->name('투자양식인쇄');
Route::post('/account/investmentpaperlist',       'Account\InvestmentController@investmentPaperList')        ->name('투자양식인쇄 list');
Route::post('/account/investmentpaperaction',     'Account\InvestmentController@investmentPaperAction')      ->name('투자양식인쇄 action');

// 투자자정보변경내역
Route::get ('/account/investorchange',            'Account\InvestorChangeController@investorChange')         ->name('투자자정보변경내역 메인');
Route::post('/account/investorchangelist',        'Account\InvestorChangeController@investorChangeList')     ->name('투자자정보변경내역 리스트');
Route::post('/account/investorchangeexcel',       'Account\InvestorChangeController@investorChangeExcel')    ->name('투자자정보변경내역 엑셀다운로드');

// 투자계약정보변경내역
Route::get ('/account/investmentchange',          'Account\InvestmentChangeController@investmentChange')     ->name('투자계약정보변경내역 메인');
Route::post('/account/investmentchangelist',      'Account\InvestmentChangeController@investmentChangeList') ->name('투자계약정보변경내역 리스트');
Route::post('/account/investmentchangeexcel',     'Account\InvestmentChangeController@investmentChangeExcel')->name('투자계약정보변경내역 엑셀다운로드');

// 양수/양도
Route::get ('/account/transfer',                  'Account\TransferController@transfer')                     ->name('양수/양도결재 메인');
Route::post('/account/transferlist',              'Account\TransferController@transferList')                 ->name('양수/양도결재 리스트');
Route::get('/account/transferform',               'Account\TransferController@transferForm')                 ->name('양수/양도결재 정보');
Route::post('/account/transferaction',            'Account\TransferController@transferAction')               ->name('양수/양도결재 저장');
Route::post('/account/transinvsearch',            'Account\TransferController@transInvSearch')               ->name('양수/양도결재 투자회원찾기');
Route::post('/account/transusrsearch',            'Account\TransferController@transUsrSearch')               ->name('양수/양도결재 투자대상찾기');
Route::post('/account/transinvlist',              'Account\TransferController@transInvList')                 ->name('양수/양도결재 투자내역');

// 원금상환으로 인한 투자원금조정
Route::get ('/account/divideorigin',              'Account\DivideOriginController@divideOrigin')             ->name('투자원금조정 메인');
Route::post('/account/divideoriginlist',          'Account\DivideOriginController@divideOriginList')         ->name('투자원금조정 리스트');
Route::post('/account/divideoriginform',          'Account\DivideOriginController@divideOriginForm')         ->name('투자원금조정 정보');
Route::post('/account/divideoriginformaction',    'Account\DivideOriginController@divideOriginFormAction')   ->name('투자원금조정 저장');
Route::post('/account/divideplusform',            'Account\DivideOriginController@dividePlusForm')           ->name('투자만기갱신 정보');
Route::post('/account/divideplusformaction',      'Account\DivideOriginController@dividePlusFormAction')     ->name('투자만기갱신 저장');
Route::post('/account/divideoriginview',          'Account\DivideOriginController@divideOriginView')         ->name('투자원금조정처리내역');

Route::post('/account/divideorigininterest',      'Account\DivideOriginController@divideOriginInterest')     ->name('투자원금조정 이자계산');

// 대출취급수수료
Route::get ('/account/handling',                  'Account\HandlingController@handling')                     ->name('취급수수료 리스트 메인');
Route::post('/account/handlinglist',              'Account\HandlingController@handlingList')                 ->name('취급수수료 리스트');
Route::get('/account/handlingform',               'Account\HandlingController@handlingForm')                 ->name('취급수수료 정보');
Route::post('/account/handlingaction',            'Account\HandlingController@handlingAction')               ->name('취급수수료 저장');
Route::post('/account/handlingsearch',            'Account\HandlingController@searchLoanInfo')               ->name('거래원장(취급수수료) 계약찾기');
Route::post('/account/handlingdelete',            'Account\HandlingController@handlingDelete')               ->name('거래원장(취급수수료) 일괄삭제');

// 원천징수
Route::get ('/account/withholding',               'Account\WithholdingController@withholding')               ->name('원천징수 메인');
Route::post('/account/withholdinglist',           'Account\WithholdingController@withholdingList')           ->name('원천징수 리스트');
Route::post('/account/withholdinglistexcel',      'Account\WithholdingController@withholdingListExcel')      ->name('원천징수 리스트 엑셀다운로드');
Route::get('/account/withholdingpop',             'Account\WithholdingController@withholdingPop')            ->name('원천징수 팝업');
Route::post('/account/withholdingexcel',          'Account\WithholdingController@withholdingExcel')          ->name('원천징수 엑셀다운로드');

// 원천징수
Route::get ('/account/advancedeposit',            'Account\AdvanceDepositController@advanceDeposit')         ->name('상품선입금리스트 메인');
Route::post('/account/advancedepositlist',        'Account\AdvanceDepositController@advanceDepositList')     ->name('상품선입금리스트 리스트');
Route::get ('/account/advancedepositform',        'Account\AdvanceDepositController@advanceDepositForm')     ->name('상품선입금등록 입력폼');
Route::post('/account/advancedepositformsearch',  'Account\AdvanceDepositController@advanceDepositSearch')   ->name('상품선입금등록 회원찾기');
Route::post('/account/advancedepositaction',      'Account\AdvanceDepositController@advanceDepositAction')   ->name('선입금등록 실행');

// 이자지급내역
Route::get('/account/interestpayment',            'Account\InterestPaymentController@interestPayment')       ->name('이자지급내역 메인');
Route::post('/account/interestpaymentlist',       'Account\InterestPaymentController@interestPaymentList')   ->name('이자지급내역 리스트');
Route::post('/account/interestpaymentdelete',     'Account\InterestPaymentController@interestPaymentDelete') ->name('이자지급내역 삭제');
Route::post('/account/interestpaymentexcel',      'Account\InterestPaymentController@interestPaymentExcel')  ->name('이자지급내역 엑셀 다운로드');

// 원금상환내역
Route::get('/account/costpayment',                'Account\CostPaymentController@costPayment')               ->name('원금상환내역 메인');
Route::post('/account/costpaymentlist',           'Account\CostPaymentController@costPaymentList')           ->name('원금상환내역 리스트');
Route::post('/account/costpaymentdelete',         'Account\CostPaymentController@costPaymentDelete')         ->name('원금상환내역 삭제');
Route::post('/account/costpaymentexcel',          'Account\CostPaymentController@costPaymentExcel')          ->name('원금상환내역 엑셀 다운로드');

// 투자자관리

// 이자지급스케줄명세
Route::get ('/account/remittance',                'Account\RemittanceController@remittance')                 ->name('이자지급스케줄명세 메인');
Route::post('/account/remittancelist',            'Account\RemittanceController@remittanceList')             ->name('이자지급스케줄명세 리스트');
Route::post('/account/remittanceexcel',           'Account\RemittanceController@remittanceExcel')            ->name('이자지급스케줄명세 엑셀다운로드');
Route::post('/account/remittanceaction',          'Account\RemittanceController@remittanceAction')           ->name('이자지급스케줄명세 송금요청');
Route::post('/account/remittancelumpinsert',      'Account\RemittanceController@remittanceLumpInsert')       ->name('이자지급스케줄명세 일괄송금요청');

// 법인관리

// 법인정보
Route::get ('/account/corporation',               'Account\CorporationController@corporation')               ->name('법인정보 메인');
Route::post('/account/corporationlist',           'Account\CorporationController@corporationList')           ->name('법인정보 리스트');
Route::get ('/account/corporationform',           'Account\CorporationController@corporationForm')           ->name('법인정보 입력폼');
Route::post('/account/corporationaction',         'Account\CorporationController@corporationAction')         ->name('법인정보 등록');
Route::post('/account/corporationexcel',          'Account\CorporationController@corporationExcel')          ->name('법인정보 엑셀다운로드');
// 주주목록
Route::get ('/account/stockholder',               'Account\StockholderController@stockholder')               ->name('주주목록 메인');
Route::post('/account/stockholderlist',           'Account\StockholderController@stockholderList')           ->name('주주목록 리스트');
Route::get ('/account/stockholderform',           'Account\StockholderController@stockholderForm')           ->name('주주목록 입력폼');
Route::post('/account/stockholderaction',         'Account\StockholderController@stockholderAction')         ->name('주주목록 등록');
Route::post('/account/stockholderexcel',          'Account\StockholderController@stockholderExcel')          ->name('주주목록 엑셀다운로드');
// 주주명부
Route::get ('/account/shareholder',               'Account\ShareholderController@shareholder')               ->name('주주명부 메인');
Route::post('/account/shareholderlist',           'Account\ShareholderController@shareholderList')           ->name('주주명부 리스트');
Route::get ('/account/shareholderform',           'Account\ShareholderController@shareholderForm')           ->name('주주명부 입력폼');
Route::post('/account/shareholderaction',         'Account\ShareholderController@shareholderAction')         ->name('주주명부 등록');
Route::post('/account/shareholderexcel',          'Account\ShareholderController@shareholderExcel')          ->name('주주명부 엑셀다운로드');
// 임원목록
Route::get ('/account/executives',                'Account\ExecutivesController@executives')                 ->name('임원목록 메인');
Route::post('/account/executiveslist',            'Account\ExecutivesController@executivesList')             ->name('임원목록 리스트');
Route::get ('/account/executivesform',            'Account\ExecutivesController@executivesForm')             ->name('임원목록 입력폼');
Route::post('/account/executivesaction',          'Account\ExecutivesController@executivesAction')           ->name('임원목록 등록');
Route::post('/account/executivesexcel',           'Account\ExecutivesController@executivesExcel')            ->name('임원목록 엑셀다운로드');

// 비용관리

// 법인차관리
Route::get ('/account/corporatecar',       'Account\CorporateCarController@corporateCar')                    ->name('법인차관리 메인');
Route::post('/account/corporatecarlist',   'Account\CorporateCarController@corporateCarList')                ->name('법인차관리 리스트');
Route::get ('/account/corporatecarform',   'Account\CorporateCarController@corporateCarForm')                ->name('법인차관리 입력폼');
Route::post('/account/corporatecaraction', 'Account\CorporateCarController@corporateCarAction')              ->name('법인차관리 등록');
Route::post('/account/corporatecarexcel',  'Account\CorporateCarController@corporateCarExcel')               ->name('법인차관리 엑셀다운로드');
// 사택관리
Route::get ('/account/companyhouse',       'Account\CompanyHouseController@companyHouse')                    ->name('사택관리 메인');
Route::post('/account/companyhouselist',   'Account\CompanyHouseController@companyHouseList')                ->name('사택관리 리스트');
Route::get ('/account/companyhouseform',   'Account\CompanyHouseController@companyHouseForm')                ->name('사택관리 입력폼');
Route::post('/account/companyhouseaction', 'Account\CompanyHouseController@companyHouseAction')              ->name('사택관리 등록');
Route::post('/account/companyhouseexcel',  'Account\CompanyHouseController@companyHouseExcel')               ->name('사택관리 엑셀다운로드');
// 임대차관리
Route::get ('/account/lease',              'Account\LeaseController@lease')                                  ->name('임대차관리 메인');
Route::post('/account/leaselist',          'Account\LeaseController@leaseList')                              ->name('임대차관리 리스트');
Route::get ('/account/leaseform',          'Account\LeaseController@leaseForm')                              ->name('임대차관리 입력폼');
Route::post('/account/leaseaction',        'Account\LeaseController@leaseAction')                            ->name('임대차관리 등록');
Route::post('/account/leaseexcel',         'Account\LeaseController@leaseExcel')                             ->name('임대차관리 엑셀다운로드');
// 보험관리
Route::get ('/account/insurance',          'Account\InsuranceController@insurance')                          ->name('보험관리 메인');
Route::post('/account/insurancelist',      'Account\InsuranceController@insuranceList')                      ->name('보험관리 리스트');
Route::get ('/account/insuranceform',      'Account\InsuranceController@insuranceForm')                      ->name('보험관리 입력폼');
Route::post('/account/insuranceaction',    'Account\InsuranceController@insuranceAction')                    ->name('보험관리 등록');
Route::post('/account/insuranceexcel',     'Account\InsuranceController@insuranceExcel')                     ->name('보험관리 엑셀다운로드');
// 계약정보관리
Route::get ('/account/contractinfo',       'Account\ContractInfoController@contractInfo')                    ->name('계약정보관리 메인');
Route::post('/account/contractinfolist',   'Account\ContractInfoController@contractInfoList')                ->name('계약정보관리 리스트');
Route::get ('/account/contractinfoform',   'Account\ContractInfoController@contractInfoForm')                ->name('계약정보관리 입력폼');
Route::post('/account/contractinfoaction', 'Account\ContractInfoController@contractInfoAction')              ->name('계약정보관리 등록');
Route::post('/account/contractinfoexcel',  'Account\ContractInfoController@contractInfoExcel')               ->name('계약정보관리 엑셀다운로드');
// 계약관리명세
Route::get ('/account/contractmanagement',       'Account\ContractManagementController@contractManagement')        ->name('계약관리명세 메인');
Route::post('/account/contractmanagementlist',   'Account\ContractManagementController@contractManagementList')    ->name('계약관리명세 리스트');
Route::get ('/account/contractmanagementform',   'Account\ContractManagementController@contractManagementForm')    ->name('계약관리명세 입력폼');
Route::post('/account/contractmanagementaction', 'Account\ContractManagementController@contractManagementAction')  ->name('계약관리명세 등록');
Route::post('/account/contractmanagementexcel',  'Account\ContractManagementController@contractManagementExcel')   ->name('계약관리명세 엑셀다운로드');
// 카드거래관리
Route::get ('/account/card',               'Account\CardController@card')                                    ->name('카드거래관리 메인');
Route::post('/account/cardlist',           'Account\CardController@cardList')                                ->name('카드거래관리 리스트');
Route::get ('/account/cardform',           'Account\CardController@cardForm')                                ->name('카드거래관리 입력폼');
Route::post('/account/cardaction',         'Account\CardController@cardAction')                              ->name('카드거래관리 등록');
Route::post('/account/cardexcel',          'Account\CardController@cardExcel')                               ->name('카드거래관리 엑셀다운로드');
// 소모품관리
Route::get ('/account/product',            'Account\ProductController@product')                              ->name('소모품관리 메인');
Route::post('/account/productlist',        'Account\ProductController@productList')                          ->name('소모품관리 리스트');
Route::get ('/account/productform',        'Account\ProductController@productForm')                          ->name('소모품관리 입력폼');
Route::post('/account/productaction',      'Account\ProductController@productAction')                        ->name('소모품관리 등록');
Route::post('/account/productexcel',       'Account\ProductController@productExcel')                         ->name('소모품관리 엑셀다운로드');

// 일정관리

// 일정관리
Route::get ('/account/calendar',           'Account\CalendarController@calendar')                            ->name('일정관리 메인');
Route::post('/account/calendarholiday',    'Account\CalendarController@calendarHoliday')                     ->name('일정관리 메인 일정리스트');
Route::post('/account/calendarinsert',     'Account\CalendarController@calendarInsert')                      ->name('일정관리 메인 일정리스트저장');

// 계좌관리

// 법인통장관리
Route::get ('/account/moacct',                 'Account\MoAcctController@moAcct')                            ->name('법인통장관리 메인');
Route::post('/account/moacctlist',             'Account\MoAcctController@moAcctList')                        ->name('법인통장관리 리스트');
Route::get('/account/moacctform',              'Account\MoAcctController@moAcctForm')                        ->name('법인통장상세 입력 form');
Route::post('/account/moacctaction',           'Account\MoAcctController@moAcctAction')                      ->name('법인통장상세 입력 action');
Route::post('/account/moacctexcel',            'Account\MoAcctController@moAcctExcel')                       ->name('법인통장상세 엑셀다운로드');
Route::get ('/account/moaccthistorypop',       'Account\MoAcctController@moAcctHistoryPop')                  ->name('법인통장관리 거래내역 팝업창');
Route::post('/account/moacctinmoneyform',      'Account\MoAcctController@moAcctInMoneyForm')                 ->name('법인통장 입금처리 입력 form');
Route::post('/account/moacctoutmoneyform',     'Account\MoAcctController@moAcctOutMoneyForm')                ->name('법인통장 출금처리 입력 form');
Route::post('/account/moacctinmoneyaction',    'Account\MoAcctController@moAcctInMoneyAction')               ->name('법인통장 입금처리 입력 action');
Route::post('/account/moacctoutmoneyaction',   'Account\MoAcctController@moAcctOutMoneyAction')              ->name('법인통장 출금처리 입력 action');
Route::post('/account/moacctdelhistoryaction', 'Account\MoAcctController@moAcctDelHistoryAction')            ->name('법인통장 거래내역 삭제 action');

// 법인통장거래내역
Route::get ('/account/moaccttrade',            'Account\MoAcctTradeController@moAcctTrade')                  ->name('법인통장거래내역 메인');
Route::post('/account/moaccttradelist',        'Account\MoAcctTradeController@moAcctTradeList')              ->name('법인통장거래내역 리스트');
Route::post('/account/moaccttradeexcel',       'Account\MoAcctTradeController@moAcctTradeExcel')             ->name('법인통장거래내역 엑셀다운로드');

// 결재업무

// 이체결재업무
Route::get ('/account/account',                'Account\AccountController@account')                          ->name('마감이체결재 메인');
Route::post('/account/accountlist',            'Account\AccountController@accountList')                      ->name('마감이체결재 리스트');
Route::post('/account/accountexcel',           'Account\AccountController@accountExcel')                     ->name('마감이체결재 엑셀');
Route::post('/account/accountlumpaction',      'Account\AccountController@accountLumpAction')                ->name('마감이체결재 일괄요청');
Route::post('/account/accountlumpdelete',      'Account\AccountController@accountLumpDelete')                ->name('마감이체결재 일괄삭제');

// 계좌실명조회
Route::post('/account/loanbanksearch',         'Account\AccountController@loanBankSearch')	                 ->name('계좌실명조회');

?>