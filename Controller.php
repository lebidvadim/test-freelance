<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
class Controller{
    use Api;
    public function __construct()
    {
        $this->apiSkills();
        $this->apiProjects();
    }
    /* Получаем проекты из БД */
    public function getProjects(){
        if(!isset($_GET['skill']))
            $projects = $this->db()->query("SELECT * FROM projects")->fetch_all(MYSQLI_ASSOC);
        else {
            $projects = $this->db()->query("
            SELECT p.* 
            FROM projects as p
            JOIN projects_skills as ps ON ps.id_proj = p.id AND ps.id_skill = ".$_GET['skill']."
            GROUP BY p.id
           ")->fetch_all(MYSQLI_ASSOC);
        }
        $skills = $this->db()->query("SELECT * FROM skills")->fetch_all(MYSQLI_ASSOC);
        $projects_skills = $this->db()->query("SELECT * FROM projects_skills")->fetch_all(MYSQLI_ASSOC);
        /* Добавляем поле skills к каждому проекту */
        $pie_chart = [
            'count' => 0,
            '500' => 0,
            '1000' => 0,
            '5000' => 0,
            '>' => 0,
        ];
        foreach ($projects as $k => $v){
            $i = 0;
            if($v['amount'] > 0) $pie_chart['count'] += 1;
            if($v['amount'] <= 500 && $v['amount'] > 0) $pie_chart['500'] += 1;
            if($v['amount'] <= 1000 && $v['amount'] > 500) $pie_chart['1000'] += 1;
            if($v['amount'] <= 5000 && $v['amount'] > 1000) $pie_chart['5000'] += 1;
            if($v['amount'] > 5000) $pie_chart['>'] += 1;
            foreach ($projects_skills as $skill){
                if($v['id'] == $skill['id_proj']) {
                    $key = array_search($skill['id_skill'], array_column($skills, 'id'));
                    $projects[$k]['skills'][$i]['id'] = $skills[$key]['id'];
                    $projects[$k]['skills'][$i]['name'] = $skills[$key]['name'];
                }
                $i++;
            }
        }
        if($pie_chart['count'] > 0 && $pie_chart['500'] > 0)
            $pie_chart['500'] = (100 / $pie_chart['count']) * $pie_chart['500'];
        if($pie_chart['count'] > 0 && $pie_chart['1000'] > 0)
            $pie_chart['1000'] = (100 / $pie_chart['count']) * $pie_chart['1000'];
        if($pie_chart['count'] > 0 && $pie_chart['5000'] > 0)
            $pie_chart['5000'] = (100 / $pie_chart['count']) * $pie_chart['5000'];
        if($pie_chart['count'] > 0 && $pie_chart['>'] > 0)
            $pie_chart['>'] = (100 / $pie_chart['count']) * $pie_chart['>'];

        return ['projects' => $projects, 'pie_chart' => $pie_chart];
    }
    /* Получаем скилы проектов для вывода ссылок для фильтрации из БД */
    public function getSkills(){
        $projects_skills = $this->db()->query("SELECT ps.id_skill as id, s.name as name FROM projects_skills as ps 
            LEFT JOIN skills as s ON s.id = ps.id_skill 
            GROUP BY ps.id_skill")->fetch_all(MYSQLI_ASSOC);
        return $projects_skills;
    }
}
trait Api{
    public function db(){
        return new mysqli('db', 'root', 'root', 'mvp');
    }
    /* Получаем проекты и записуем к нам в БД */
    public function apiProjects(){
        $result = $this->apiConnect('https://api.freelancehunt.com/v2/projects');
        if(array_key_exists('data', $result)) {
            $privatbank = $this->apiConnect('https://api.privatbank.ua/p24api/pubinfo?json&coursid=5');
            foreach ($result['data'] as $item) {
                $check = $this->db()->query("SELECT `id` FROM `projects` WHERE `id` = '".$item['id']."'");
                if (mysqli_num_rows($check) === 0) {
                    $amount = 0;
                    $currency = null;
                    $amount_usd = 0;
                    if($item['attributes']['budget'] != null){
                        $amount = $item['attributes']['budget']['amount'];
                        $currency = $item['attributes']['budget']['currency'];
                        $amount_usd = round($item['attributes']['budget']['amount'] / $privatbank[1]['buy']);
                    }
                    /* Записуем проект в БД */
                    $this->db()->query("INSERT INTO `projects`
                    (`id`, `name`,`link`,`amount`,`amount_usd`,`currency`,`login`,`first_name`,`last_name`) 
                    VALUES 
                    (" . $item['id'] . ",'" . $item['attributes']['name'] . "','".$item['links']['self']['web']."','".$amount."','".$amount_usd."','".$currency."','" . $item['attributes']['employer']['login'] . "','" . $item['attributes']['employer']['first_name'] . "','" . $item['attributes']['employer']['last_name'] . "')");
                    /* Проверяем есть ли скилы к проекту, если есть то записуем скилы к проекту в БД */
                    if($item['attributes']['skills'] != null){
                        foreach ($item['attributes']['skills'] as $skill){
                            $this->db()->query("INSERT INTO `projects_skills` (`id_proj`, `id_skill`) VALUES 
                            ('".$item['id']."','".$skill['id']."')");
                        }
                    }

                }
            }
        }
    }
    /* Получаем Скилы и записуем к нам в БД */
    public function apiSkills(){
        $result = $this->apiConnect('https://api.freelancehunt.com/v2/skills');
        if(array_key_exists('data', $result)) {
            foreach ($result['data'] as $item) {
                $check = $this->db()->query("SELECT `id` FROM skills WHERE `id` = '".$item['id']."'");
                if (mysqli_num_rows($check) === 0)
                    $this->db()->query("INSERT INTO skills (`id`, `name`) VALUES (".$item['id'].", '".$item['name']."')");
            }
            return true;
        }
        return false;
    }
    private function apiConnect(string $url):array{
        $out = [];
        if( $curl = curl_init() ) {
            $authorization = "Authorization: Bearer 5ec1bec4dbbebd228d45074b8e6207f008ade073";
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $out = curl_exec($curl);
            $out = json_decode($out,true);
            curl_close($curl);
        }
        return $out;
    }
}
$app = new Controller();