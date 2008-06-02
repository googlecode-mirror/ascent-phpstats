<?php
require_once ("pomm_cfg.php");
require_once ("stat.inc.php");

##################
######CONFIG#####
##################
$auth=array(
'web'=>'localhost/Cache',
'login'=>'change this',
'pass'=>'change this',
'realmid'=>0
);


//$auth['web']="localhost/ascent_stats/svn/Cache";

//echo 'var $data=array(';
//foreach(glob("./img/*.*") as $file){
//	echo "array(\"f\"=>\"{$file}\",\"md5\"=>\"".md5_file($file)."\"),";
//}
//echo ');';
//exit;
// $realm_name = get_realm_name();

if( !class_exists('gtk')) {
    die('Please load the php-gtk2 module in your php.ini' . "\r\n");
}


class pomm_gui extends GtkWindow
{
    protected $da;
    protected $wp;
    protected $stats;
    protected $pixmap;
    protected $data=array();
    protected $inputHTTP;
    protected $p=array("x"=>0,"y"=>0,"obj"=>0);
    protected $sinfo;
    protected $m_ssdata;
    protected $online_a;
    protected $online_h;
    protected $is_lock=false;
    public $ss="";
    function __construct($parent = null)
    {
        parent::__construct();

        if ($parent)
            $this->set_screen($parent->get_screen());
        else
            $this->connect_simple('destroy', array('gtk', 'main_quit'));

        $this->set_title("Pomm");
        $this->set_position(Gtk::WIN_POS_CENTER);
        $this->set_default_size(850+100, 600+50);
        $this->set_border_width(0);
        $this->set_resizable(false);
        $pin = new GtkHPaned();
        $pin->set_position(784);
	$vbox = new GtkVBox(false, 5);
	$this->add($vbox);
        $vbox->set_border_width(5);
	
	$pin->add1($this->__create_box());
	$pin->add2($this->__create_dbox());
	$vbox->pack_start($pin);

        $button = new GtkButton('Update');
        $button->connect('clicked', array($this, '__update'));
        $button2 = new GtkButton('Filter');
        $button2->connect('clicked', array($this, 'string_find'));
	$button3=new GtkButton("LIST");
	$button3->connect('clicked', array($this, 'set_mode_stat'));
	
        $bbox = new GtkHBox(false, 5);        
        $bbox->pack_start($button);
        $bbox->pack_start($button2);
        $bbox->pack_start($button3);


	$this->sinfo = new GtkLabel();
        $this->sinfo->set_markup('x:0 y:0 obj:0');
        $paned = new GtkHPaned();
        $paned->set_position(65*3);
	$paned->add1($bbox);
	$paned->add2($this->sinfo);
        $vbox->pack_end($paned, false, false, 0);

	//$this->set_icon_from_file("./img/4-1.gif");
        $this->show_all();
    }

    
    
    function __create_box()
    {
        //$vbox = new GtkVBox(false, 5);
        //$vbox->set_border_width(0);
 
	$this->da = new GtkDrawingArea();
	$this->da->set_size_request(784+75, 525);
	$this->da->connect('expose_event'       , array($this, 'expose_event'));
        $this->da->connect('configure_event'    , array($this, 'configure_event'));

        $this->da->connect('motion_notify_event', array($this, 'motion_notify_event'));
        $this->da->connect('button_press_event' , array($this, 'button_press_event'));
        $this->da->set_events(Gdk::EXPOSURE_MASK
                                | Gdk::LEAVE_NOTIFY_MASK
                                | Gdk::BUTTON_PRESS_MASK
                                | Gdk::POINTER_MOTION_MASK
                                | Gdk::POINTER_MOTION_HINT_MASK);

        //$scrwndImg = new GtkScrolledWindow();
	//$scrwndImg->set_resize_mode(Gtk::RESIZE_PARENT);
        //$scrwndImg->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
	//$scrwndImg->set_size_request(784, 525);        
	//$vbox->pack_start($scrwndImg);
	//$scrwndImg->add_with_viewport($this->da);
	//$vbox->pack_start($this->da,false,false,0);
	//$vbox->pack_start($this->__create_dbox(),false,false,0);

	//$lbox = new 
	//$vbox->pack_start($this->da);

        //$entry = new GtkEntry();
        //$vbox->pack_end($entry, false, false, 0);
        
        //$completion = new GtkEntryCompletion();
        //$completion_model = $this->__create_completion_model();
        //$completion->set_model($completion_model);
        //$completion->set_text_column(0);
        
        //$entry->set_completion($completion);
	//var_dump($vbox);
        return $this->da;
        //return $vbox;
    }
    function __create_dbox(){
	$vbox = new GtkVBox(false, 5);
	$vbox->set_spacing(2);
	$this->m_ssdata=array();
	for($i=0;$i<=14;$i++){
		$this->m_ssdata[$i]=array();
		$this->m_ssdata[$i]['Img1'] = GtkImage::new_from_file("./img/11-1.gif");
		$this->m_ssdata[$i]['Img2'] = GtkImage::new_from_file("./img/9.gif");
		$this->m_ssdata[$i]['paned'] = new GtkTable(1, 2, false);
		$this->m_ssdata[$i]['paned']->attach($this->m_ssdata[$i]['Img1'], 0, 1, 1, 2);
		$this->m_ssdata[$i]['paned']->attach($this->m_ssdata[$i]['Img2'], 1, 2, 1, 2);
		$this->m_ssdata[$i]['paned']->set_col_spacings(1);
		$this->m_ssdata[$i]['paned']->set_row_spacings(1);
		//$this->m_ssdata[$i]['paned'] = new GtkHPaned();
		//$this->m_ssdata[$i]['paned']->add1($this->m_ssdata[$i]['Img1']);
		//$this->m_ssdata[$i]['paned']->add2($this->m_ssdata[$i]['Img2']);
		//$this->m_ssdata[$i]['paned']->set_position(45);
		$vbox->pack_start($this->m_ssdata[$i]['paned'],false,false,0);
		$this->m_ssdata[$i]['name'] = new GtkLabel();
		$this->m_ssdata[$i]['name']->set_markup("Name: \nZacki");
		$vbox->pack_start($this->m_ssdata[$i]['name']);
		$this->m_ssdata[$i]['level'] = new GtkLabel();
		$this->m_ssdata[$i]['level']->set_markup("Level: 70");
		$vbox->pack_start($this->m_ssdata[$i]['level']);
		$this->m_ssdata[$i]['ping'] = new GtkLabel();
		$this->m_ssdata[$i]['ping']->set_markup("Ping: 70");
		$vbox->pack_start($this->m_ssdata[$i]['ping']);
		$this->m_ssdata[$i]['map'] = new GtkLabel();
		$this->m_ssdata[$i]['map']->set_markup("Zone: \nXZ");
		$vbox->pack_start($this->m_ssdata[$i]['map']);

		$this->m_ssdata[$i]['end'] = new GtkLabel();
		$this->m_ssdata[$i]['end']->set_markup("-----");
		$vbox->pack_start($this->m_ssdata[$i]['end']);
	}
	$scrolled = new GtkScrolledWindow();
	$scrolled->add_with_viewport($vbox);
	$scrolled->set_policy(Gtk::POLICY_NEVER,Gtk::POLICY_AUTOMATIC);	
	$this->m_ssdata_clear();
	return $scrolled;
    }
    function m_ssdata_clear(){
	for($i=0;$i<=14;$i++){
		if(is_array($this->m_ssdata[$i])){
			$this->m_ssdata[$i]['name']->set_markup("");
			$this->m_ssdata[$i]['level']->set_markup("");
			$this->m_ssdata[$i]['ping']->set_markup("");
			$this->m_ssdata[$i]['map']->set_markup("");
			$this->m_ssdata[$i]['Img1']->set_from_pixbuf(null);
			$this->m_ssdata[$i]['Img2']->set_from_pixbuf(null);

			$this->m_ssdata[$i]['name']->hide();
			$this->m_ssdata[$i]['level']->hide();
			$this->m_ssdata[$i]['ping']->hide();
			//$this->m_ssdata[$i]['end']->hide();
			$this->m_ssdata[$i]['map']->hide();
			$this->m_ssdata[$i]['Img1']->hide();
			$this->m_ssdata[$i]['Img2']->hide();
			$this->m_ssdata[$i]['paned']->hide();
		}
	}
    }
    function m_ssdata_set($ar){
	$im=count($ar);
	if($im>14)$im=14;
	$this->m_ssdata_clear();
	for($i=0;$i<=$im;$i++){
		if(!empty($ar[$i]['name']) AND is_array($this->m_ssdata[$i])){
			$this->m_ssdata[$i]['name']->show();
			$this->m_ssdata[$i]['level']->show();
			$this->m_ssdata[$i]['ping']->show();
			$this->m_ssdata[$i]['end']->show();
			$this->m_ssdata[$i]['map']->show();
			$this->m_ssdata[$i]['Img1']->show();
			$this->m_ssdata[$i]['Img2']->show();
			$this->m_ssdata[$i]['paned']->show();
			$z=get_zone_name($ar[$i]['map'],$ar[$i]["xpos"],$ar[$i]["ypos"]);
			if($z==" ")$z=get_map_name($ar[$i]['map']);
			$this->m_ssdata[$i]['map']->set_markup("Zone: \n".$z);
			$this->m_ssdata[$i]['name']->set_markup("Name: \n{$ar[$i]['name']}");
			$this->m_ssdata[$i]['level']->set_markup("Level: {$ar[$i]['level']}");
			$this->m_ssdata[$i]['ping']->set_markup("Ping: {$ar[$i]['latency']}");
			$this->m_ssdata[$i]['Img1']->set_from_file("./img/{$ar[$i]['race']}-{$ar[$i]['gender']}.gif");
			$this->m_ssdata[$i]['Img2']->set_from_file("./img/{$ar[$i]['class']}.gif");
		}
	}
    }
    function __create_completion_model()
    {
        $this->inputHTTP = $store = new GtkListStore(Gtk::TYPE_STRING);
        
        $iter = $store->append();
        $store->set($iter, 0, 'GNOME');
        
        return $store;
    }
    function configure_event($widget, $event)
    {
        $this->pixmap = new GdkPixmap($widget->window,
                                $widget->allocation->width,
                                $widget->allocation->height,
                                -1);
        $this->pixmap->draw_rectangle($widget->style->white_gc,
                            true, 0, 0,
                            $widget->allocation->width,
                            $widget->allocation->height);
	$this->__update();
        return true;
    }
    function get_point($x,$y){
	$data=$this->data;
	$rt=array();
	if(!is_array($data) OR count($data)<1)return $rt;
	foreach($data as $k=>$v){
		if($v["x"]<$x AND $v["x"]+7>$x) {
			if ($v["y"]<$y AND $v["y"]+7>$y){
				if($this->ss=="")
				$rt[]=$v;
				elseif(sString($v['name'],$this->ss))
				$rt[]=$v;
			}
		}
	}
	return $rt;
    }
    function update_data($http){
	$f=unserialize(file_get_contents($http));
	$data=$f['data']['sessions']['plr'];
	$this->online_a=0;
	$this->online_h=0;
	if(!is_array($data) OR count($data)<1){
		$this->data=array();
         	$dialog = new GtkMessageDialog($this, Gtk::DIALOG_MODAL | Gtk::DIALOG_DESTROY_WITH_PARENT, Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK,sprintf("ошибка при полученние данных или онлан 0 с источника:\n%s",$http));
        	$dialog->run();
        	$dialog->destroy();
		return;}
	$badchar=array();
	foreach($data as $k => $v){
		
		$pos=get_player_position($v["xpos"],$v["ypos"],$v["map"]);
		if($v["map"]==568){
			$pos["x"]=673;
			$pos["y"]=106;
		}
		$data[$k]["x"]=$pos["x"];
		$data[$k]["y"]=$pos["y"];
		$r = $v['race'];
		if($r==1 OR $r==3 OR $r==4 OR $r==7 OR $r==11)
		{ 
			$data[$k]["f"]="a";
			$this->online_a++;
		}else{
			$data[$k]["f"]="h";
			$this->online_h++;
		}
		if($pos["x"]<=5 AND $pos["y"]<=5){
			$badchar[]=array('data'=>$v,'msg'=>"Чар в координатах на карте X:0-5 Y:0-5 т.е. не прописана лока/инстант скорее всего, mapid:{$v['map']}");
			//unset($data[$k]);
		}
	}
	if(count($badchar)>0){
		$txt="Error:\n";
		foreach($badchar as $vl){
			$txt.="X: {$vl['data']['x']} Y: {$vl['data']['y']} MAP: {$vl['data']['map']} Name: {$vl['data']['name']}\n";
			//$txt.="MSG: {$vl['msg']}\n\n";
		}
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_MODAL | Gtk::DIALOG_DESTROY_WITH_PARENT, Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK,$txt);
        	$dialog->run();
        	$dialog->destroy();
	}
	$this->data=$data;
    }
    function set_mode_stat($button){
	$this->wp = new StockItemBrowserStat($this);
	if($this->wp) $this->wp->__update($this->data);
	//$button->connect('clicked', array($this, 'set_mode_map'));
	//$button->set_label("MAP MODE");
	//$this->wp->hide();
	//$this->wp->add1(NULL);//$this->stat
	//$this->wp->show();
    }
    function set_mode_map($button){
	$button->connect('clicked', array($this, 'set_mode_stat'));
	$button->set_label("STAT MODE");
	$this->wp->hide();
	$this->wp->add1($this->da);
	$this->wp->show();
    }
    function string_find($button){

        $dialog = new GtkDialog('Interactive Dialog', $this, 0, array( Gtk::STOCK_OK, Gtk::RESPONSE_OK));
    
        $hbox = new GtkHBox(false, 8);
        $hbox->set_border_width(8);
        $dialog->vbox->pack_start($hbox, false, false, 0);
    
        $stock = GtkImage::new_from_stock(
                Gtk::STOCK_DIALOG_QUESTION,
                Gtk::ICON_SIZE_DIALOG);
        $hbox->pack_start($stock, false, false, 0);
    
        $table = new GtkTable(2, 2);
        $table->set_row_spacings(4);
        $table->set_col_spacings(4);
        $hbox->pack_start($table, true, true, 0);
    
        $label = new GtkLabel('Name for find');
        $label->set_use_underline(true);
        $table->attach($label, 0, 1, 0, 1);
        $local_entry1 = new GtkEntry();
        $local_entry1->set_text($this->ss);
        $table->attach($local_entry1, 1, 2, 0, 1);
        $label->set_mnemonic_widget($local_entry1);
    
        $dialog->show_all();
    
        $response = $dialog->run();
    
        if ($response == Gtk::RESPONSE_OK) {
            $this->ss=$local_entry1->get_text();
            $this->__update(NULL,true);
        }
        $dialog->destroy();
    }
    function __update($bt=NULL,$passive=false){
	global $auth;
	if(!$passive){
		$fdata=file_get_contents("http://{$auth['login']}:{$auth['pass']}@{$auth['web']}/sess_system.php");
		$mdata=@unserialize($fdata);
		//var_dump($fdata,$mdata);

		$this->update_data("http://{$auth['web']}/XML/sess_xml-stats".$mdata['serv'][$auth['realmid']]['sid']);

		//$this->update_data("http://127.0.0.1/ascent_stats/svn/Cache/XML/sess_xml-stats".$mdata['serv'][0]['sid']);
	}
        	$widget=$this->da;
	$this->m_ssdata_clear();
	$this->is_lock=false;
	$this->update_sinfo(true);
	$this->pixmap->draw_pixbuf($widget->style->black_gc,GdkPixbuf::new_from_file("./img/map.jpg"),0,0,0,0);
	$data=$this->data;
	if(is_array($data) AND count($data)>1){
		foreach($data as $key=>$val){
			if($this->ss=="")
				$this->pixmap->draw_pixbuf($widget->style->black_gc,GdkPixbuf::new_from_file("./img/{$val['f']}_point.gif"),0,0,$val['x'],$val['y']);
			elseif(sString($val['name'],$this->ss))
				$this->pixmap->draw_pixbuf($widget->style->black_gc,GdkPixbuf::new_from_file("./img/{$val['f']}_point.gif"),0,0,$val['x'],$val['y']);
			
		}
	}
	$widget->queue_draw();
	if($this->wp) $this->wp->__update($this->data);
    }

    function expose_event($widget, $event)
    {
        $widget->window->draw_drawable($widget->style->fg_gc[$widget->state],
                        $this->pixmap,
                        $event->area->x, $event->area->y,
                        $event->area->x, $event->area->y,
                        $event->area->width, $event->area->height);
        return false;
    }



    function button_press_event($widget, $event)
    {
        if ($event->button == 1 && $this->pixmap) {
		if($this->is_lock==true)$this->is_lock=false; else $this->is_lock=true;
		$this->update_sinfo(true);
            //$this->draw_brush($widget, (int)$event->x, (int)$event->y);
        }

        return true;
    }


    function update_sinfo($ignore_rules=false){
	if(!$this->is_lock or $ignore_rules)
	$this->sinfo->set_markup(sprintf("Cursor(X:%u Y:%u OBJ:%u%s%s) Online:%u(A:%u H:%u)",$this->p["x"],$this->p["y"],$this->p["obj"],$this->is_lock==true ? " LOCK" : "",$this->ss=="" ? "" : " FILTER",$this->online_a+$this->online_h,$this->online_a,$this->online_h));
    }
    function motion_notify_event($widget, $event)
    {
        $window  = $event->window;
        $pointer = $window->get_pointer();
        $x = $pointer[0];
        $y = $pointer[1];
        $state = $pointer[2];
	$p=$this->get_point($x,$y);
	if(!$this->is_lock){
		$this->p["x"]=$x;
		$this->p["y"]=$y;
		$this->p["obj"]=count($p);
		//if(count($p)>0)
			$this->m_ssdata_set($p);
		$this->update_sinfo();
	}
        if (($state & Gdk::BUTTON1_MASK) && $this->pixmap) {
	    //var_dump($pointer);
            //$this->draw_brush($widget, $x, $y);
        }

        return true;
    }



    function draw_brush($widget, $x, $y)
    {
	//$widget->style->black_gc
        $this->pixmap->draw_arc($widget->style->black_gc, true, $x - 4, $y - 4, 8, 8, 0, 64 * 360);
        $widget->queue_draw_area($x - 4, $y - 4, 8, 8);
    }
}
class resguard
{
	var $data=array(array("f"=>"./img/1-0.gif","md5"=>"6875a3b1ae7fcece0c0ca30efb69a2ea"),array("f"=>"./img/1-1.gif","md5"=>"bf9bd4c4297efe448bc601d4733c6aae"),array("f"=>"./img/1.gif","md5"=>"77539304703af00875ab85fdc23b1ba2"),array("f"=>"./img/10-0.gif","md5"=>"29b2b6df27285299df798e0778a91ef5"),array("f"=>"./img/10-1.gif","md5"=>"41b3dffaf5faea2d24238c656dbe7ea4"),array("f"=>"./img/11-0.gif","md5"=>"38ddfb36aa00a93e4308d12c1a839eaf"),array("f"=>"./img/11-1.gif","md5"=>"5d53b707178d524d290b936adf702d90"),array("f"=>"./img/11.gif","md5"=>"0ed4e75f9640e5ba50886339b1490b86"),array("f"=>"./img/2-0.gif","md5"=>"783e24146c47c434a3fa49e4788d4e15"),array("f"=>"./img/2-1.gif","md5"=>"71bdcdf642c5e3158a023bbf393a0885"),array("f"=>"./img/2.gif","md5"=>"5a1d5013fbb7261aaa00cbec581ac770"),array("f"=>"./img/3-0.gif","md5"=>"c053750bf94f397d572e76a52ee7e77b"),array("f"=>"./img/3-1.gif","md5"=>"870b90d6a3f29a935422a039f76bd5ce"),array("f"=>"./img/3.gif","md5"=>"17c1cb10d86a90d04a637f7d79ff5888"),array("f"=>"./img/4-0.gif","md5"=>"26c1f267a200eccd47cb05c1243b059c"),array("f"=>"./img/4-1.gif","md5"=>"e7968739687ef2d342cb2a8a57a127d7"),array("f"=>"./img/4.gif","md5"=>"322ed34df5b72ddb8da18348c620c47c"),array("f"=>"./img/5-0.gif","md5"=>"82255cce8dbbc56cf745f71315678e0f"),array("f"=>"./img/5-1.gif","md5"=>"ec54085adda83497647a0d8226deb288"),array("f"=>"./img/5.gif","md5"=>"62801cad7e2644f39836c228d39fe7c8"),array("f"=>"./img/6-0.gif","md5"=>"2ff460a190feaa5f4a51495e7ba6f206"),array("f"=>"./img/6-1.gif","md5"=>"ad207c49b893a6c9d124ab7cb3edb90e"),array("f"=>"./img/7-0.gif","md5"=>"67e780ebdb996d35589249fb0a3c8cc1"),array("f"=>"./img/7-1.gif","md5"=>"831437729b3d86c925e0c9d213df2285"),array("f"=>"./img/7.gif","md5"=>"8b74eb2d76b8701438348442f89e4798"),array("f"=>"./img/8-0.gif","md5"=>"8126a002c8836b161ba03bcb627e0dc6"),array("f"=>"./img/8-1.gif","md5"=>"c0c8f1651deef8e9934e70ddfaeaac06"),array("f"=>"./img/8.gif","md5"=>"553f6a038030c7f0cf21e7eadae31cd8"),array("f"=>"./img/9.gif","md5"=>"7d38911c6c08aadec4c5794841d14e8a"),array("f"=>"./img/a_point.gif","md5"=>"333961c2f8264becd130c05aa4ff822d"),array("f"=>"./img/h_point.gif","md5"=>"bd0294d14235dd26afe5c1fa92534746"),array("f"=>"./img/index.html","md5"=>"558a872f2a3f6c737ad7c0f115a053d1"),array("f"=>"./img/map.jpg","md5"=>"2445d23ceaba81508e83dbfc78e956cb"));
	var $cg=0;
	var $txt="Erros:\n\n";
	function __construct(){
		$d=$this->data;
		foreach($d as $v){
			if(@md5_file($v["f"])!=$v["md5"]) $this->txt.="Invalid faile {$v['f']}\n";
			else $this->cg++;
		}
		if(count($this->data)!=$this->cg){
			
         			$dialog = new GtkMessageDialog(null, Gtk::DIALOG_MODAL | Gtk::DIALOG_DESTROY_WITH_PARENT, Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK,$this->txt);
        			$dialog->run();
        			$dialog->destroy();
			exit;
		}
	}
}
function sString($txt,$str)
{
        $txt=strtolower($txt);
        $str=strtolower($str);
	if(empty($txt)){return false;}
	for($i=0;$i<strlen($str);$i++)
	{
		if($txt{$i} != $str{$i}){return false;}
	}
	return true;
}


new resguard();
$th = new pomm_gui();
Gtk::main();
?>
