<?php
/**
 * Created by PhpStorm.
 * User: Ankit
 * Date: 6/25/2015
 * Time: 11:17 PM
 */
error_reporting(0);
class OpenIrctc {
    private $pnr_id, $train_number;
    protected $pnr_h_url = 'http://www.indianrail.gov.in/cgi_bin/inet_pnrstat_cgi_hindi.cgi';
    protected $pnr_e_url = 'http://www.indianrail.gov.in/cgi_bin/inet_pnstat_cgi_2484.cgi';
    protected $schedule_e_url = 'http://www.indianrail.gov.in/cgi_bin/inet_trnnum_cgi.cgi';
    protected $pnr_url, $postFields;
    public function __construct($pnr_id = null){
        $this->pnr_id = $pnr_id;
    }

    public function set_language($language = "english"){
        if(strtolower($language) === 'english'){
            $this->pnr_url = $this->pnr_e_url;
            $this->postFields = 'lccp_pnrno1='. $this->pnr_id .'&lccp_cap_value=25000&lccp_capinp_value=25000';
        } elseif(strtolower($language) === 'hindi'){
            $this->pnr_url = $this->pnr_h_url;
            $this->postFields = 'lccp_pnrno1='. $this->pnr_id .'&submit=Get PNR Status';
        }
    }

    public function pnr_full_check($pnr_id = null){
		if($pnr_id != null){
			$this->pnr_id = $pnr_id;
		}
        if(!function_exists('curl_init')){
            die('Curl module is required to use this library');
        }
        //return $this->call_pnr_url();exit;
        return $this->reader_array($this->call_pnr_url());
    }

    protected function call_pnr_url(){

        $http_headers = array(
            'User-Agent:Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:43.0) Gecko/20100101 Firefox/43.0',
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language:en-US,en;q=0.8,hi;q=0.6,mr;q=0.4',
            'Referer:http://www.indianrail.gov.in/cgi_bin/inet_pnstat_cgi_2484.cgi',
            'Origin:http://www.indianrail.gov.in',
            'Host:www.indianrail.gov.in',
            'Content-Type:application/x-www-form-urlencoded',
            'Connection:keep-alive',
        );
        $curl = curl_init($this->pnr_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2438.3 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postFields);
        $result =  curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    protected function reader_array($html){
        $htmlDom = new DOMDocument();
        $htmlDom->loadHTML($html);
        $finder = new DOMXPath($htmlDom);
        //echo $html;
        $pnr_status_classname= 'table_border_both';
        $pnr_status_info = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $pnr_status_classname ')]");

        $data = array();
        if(isset($pnr_status_info->item(0)->textContent)){
            $data["$this->pnr_id"]['status'] = "success";
            $this->train_number = preg_replace("/[^0-9]/", "", $pnr_status_info->item(0)->textContent);
        } else {
            $data["$this->pnr_id"]['status'] = "failed";
        }
        $data["$this->pnr_id"]['train_info']['train_no'] = $pnr_status_info->item(0)->textContent;
        $data["$this->pnr_id"]['train_info']['train_name'] = $pnr_status_info->item(1)->textContent;
        $data["$this->pnr_id"]['train_info']['train_date'] = $pnr_status_info->item(2)->textContent;
        $data["$this->pnr_id"]['train_info']['train_from'] = $pnr_status_info->item(3)->textContent;
        $data["$this->pnr_id"]['train_info']['train_to'] = $pnr_status_info->item(4)->textContent;
        $data["$this->pnr_id"]['train_info']['train_res_to'] = $pnr_status_info->item(5)->textContent;
        $data["$this->pnr_id"]['train_info']['train_res_from'] = $pnr_status_info->item(6)->textContent;
        $data["$this->pnr_id"]['train_info']['train_res_class'] = $pnr_status_info->item(7)->textContent;

        $datasize = $pnr_status_info->length;
        for($i=8; $i< $datasize - 3; $i = $i + 3){
            $data["$this->pnr_id"][$pnr_status_info->item($i)->textContent]['booking_status'] = $pnr_status_info->item($i+1)->textContent;
            $data["$this->pnr_id"][$pnr_status_info->item($i)->textContent]['current_status'] = $pnr_status_info->item($i+2)->textContent;
        }
        $data["$this->pnr_id"][$pnr_status_info->item($i)->textContent]['charting_status'] = $pnr_status_info->item($datasize-2)->textContent;
        return $data;
    }


    public function get_train_schedule($train_number = null){
        if($train_number != null){
            $this->train_number = $train_number;
        }

        $html_data = $this->call_trains_url();
        $htmlDom = new DOMDocument();
        $htmlDom->loadHTML($html_data);
        $finder = new DOMXPath($htmlDom);
        $pnr_status_classname= 'table_border_both';
        $schedulerider = $finder->query("//table[@class='table_border_both']/tr/*");
        $schedulesize = $schedulerider->length;

        if(isset($schedulerider->item(0)->textContent)){
            $train_info[$train_number]['status'] = "success";
        } else {
            $train_info[$train_number]['status'] = "failed";
        }
        $data = array();
        $days_array = array('MON','TUE','WED','THU','FRI','SAT','SUN');
        $train_info[$train_number]['train_no'] = $schedulerider->item(4)->textContent;
        $train_info[$train_number]['train_name'] = $schedulerider->item(5)->textContent;
        $train_info[$train_number]['train_from'] = $schedulerider->item(6)->textContent;

        $full_length = $schedulerider->length;


        $f_tabl_end = 0;
        for($i=7; $i<=14; $i++){
            if(!in_array(trim($schedulerider->item($i)->textContent), $days_array)){
                $f_tabl_end = $i-1;
                break;
            }
        }
        $new_tbl_data_bi = $f_tabl_end+11;
        for($i=$new_tbl_data_bi;$i<=$full_length-1;$i=$i+9){
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['stn_code'] = $schedulerider->item($i+1)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['stn_name'] = $schedulerider->item($i+2)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['route_no'] = $schedulerider->item($i+3)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['arrival_time'] = $schedulerider->item($i+4)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['dep_time'] = $schedulerider->item($i+5)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['halt_time'] = $schedulerider->item($i+6)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['distance'] = $schedulerider->item($i+7)->textContent;
            $train_info[$train_number]['halts'][$schedulerider->item($i)->textContent]['day'] = $schedulerider->item($i+8)->textContent;

            $last_skip_check = trim($schedulerider->item($i+9)->textContent);
            if(!is_numeric($last_skip_check)){
                $i++;
            }
        }
        return $train_info;
    }

    private function call_trains_url(){
        $http_headers = array(
            'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language:en-US,en;q=0.8,hi;q=0.6,mr;q=0.4',
            'Referer:http://www.indianrail.gov.in/cgi_bin/inet_pnstat_cgi_10521.cgi',
            'Origin:http://www.indianrail.gov.in',
            'Host:www.indianrail.gov.in',
            'Content-Type:application/x-www-form-urlencoded',
            'Connection:keep-alive',
        );
        $curl = curl_init($this->schedule_e_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2438.3 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'lccp_trnname='. $this->train_number .'&getIt=Please Wait...');
        $result =  curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    

}
