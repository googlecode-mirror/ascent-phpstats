<?php
class StockItemBrowserStat extends GtkWindow {
    var $list;
    function __construct($parent = null)
    {
        parent::__construct();

        if ($parent)
            $this->set_screen($parent->get_screen());
        else
            $this->connect_simple('destroy', array('gtk', 'main_quit'));

        $this->set_title(__CLASS__);
        $this->set_position(Gtk::WIN_POS_CENTER);
        $this->set_default_size(-1, 500);
        $this->set_border_width(8);

        $hbox = new GtkHBox(false, 8);
        $this->add($hbox);

        $scrolled = new GtkScrolledWindow();
        $scrolled->set_shadow_type(Gtk::SHADOW_ETCHED_IN);
        $scrolled->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
        $hbox->pack_start($scrolled, false, false, 0);

        $this->list = new GtkListStore(Gtk::TYPE_STRING, GdkPixbuf::gtype, GdkPixbuf::gtype, Gtk::TYPE_LONG, Gtk::TYPE_LONG, Gtk::TYPE_STRING);
        $treeview = new GtkTreeView($this->list);
        $scrolled->add($treeview);

        $column = new GtkTreeViewColumn();
        //$column->set_title('Name');

        $cell_renderer = new GtkCellRendererText();
        //$treeview->insert_column_with_data_func(-1, 'Name', $cell_renderer, 'smart_setter');
        $treeview->append_column(new GtkTreeViewColumn('Name', $cell_renderer, 'text', 0));

        $cell_renderer = new GtkCellRendererPixbuf();
 $treeview->append_column(new GtkTreeViewColumn('Race', $cell_renderer, 'pixbuf', 1));
        //$treeview->insert_column_with_data_func(-1, 'Race', $cell_renderer, 'smart_setter');

        $cell_renderer = new GtkCellRendererPixbuf();
 $treeview->append_column(new GtkTreeViewColumn('Class', $cell_renderer, 'pixbuf', 2));
        //$treeview->insert_column_with_data_func(-1, 'Class', $cell_renderer, 'smart_setter');

        $cell_renderer = new GtkCellRendererText();
        //$treeview->insert_column_with_data_func(-1, 'Zone', $cell_renderer, 'smart_setter');

        $cell_renderer = new GtkCellRendererText();
 $treeview->append_column(new GtkTreeViewColumn('Level', $cell_renderer, 'text', 3));
        //$treeview->insert_column_with_data_func(-1, 'LEVEL', $cell_renderer, 'smart_setter');

        $cell_renderer = new GtkCellRendererText();
 $treeview->append_column(new GtkTreeViewColumn('Ping', $cell_renderer, 'text', 4));
        //$treeview->insert_column_with_data_func(-1, 'PING', $cell_renderer, 'smart_setter');

 $treeview->append_column(new GtkTreeViewColumn('Zone', $cell_renderer, 'text', 5));
        $align = new GtkAlignment(0.5, 0, 0, 0);
        $hbox->pack_end($align, true, true, 0);

        $selection = $treeview->get_selection();
        $selection->set_mode(Gtk::SELECTION_SINGLE);

        //$selection->connect('changed', array($this, 'on_selection_changed'));
        $treeview->connect('key-press-event', array($this, 'on_selection_changed'));
        $treeview->connect('button-press-event', array($this, 'on_selection_changed'));

        $this->show_all();
    }
	function __update($data){
		$this->list->clear();
		foreach($data as $char){
			$z=get_zone_name($char['map'],$char["xpos"],$char["ypos"]);
			if($z==" ")$z=get_map_name($char['map']);
			$this->list->append(array($char['name'],GdkPixbuf::new_from_file("./img/{$char['race']}-{$char['gender']}.gif"),GdkPixbuf::new_from_file("./img/{$char['class']}.gif"),$char['level'],$char['latency'],$z));
		}
		$this->list->set_sort_column_id(0, Gtk::SORT_ASCENDING);
	}
	function on_selection_changed($vFile, $event)
	{
		global $th;
		if (($event->type == Gdk::KEY_PRESS && $event->keyval == Gdk::KEY_Return) || ($event->type == Gdk::_2BUTTON_PRESS && $event->button == 1)) {
			list($model, $iter) = $vFile->get_selection()->get_selected();
			if ($iter) {
				$th->ss = $model->get_value($iter, 0);
				$th->__update(null,true);
				return true;
			}
		}
	}
}

function smart_setter($column, $cell, $model, $iter)
{
    $info = $model->get_value($iter, 0);
    $cell->set_property('text', $info->constant);
}

class StockItemInfo {
    public $stock_id = '';
    public $stock_item = null;
    public $small_icon = null;
    public $constant = '';
    public $accel_str = '';

    function __construct($stock_id = null) {
        $this->stock_id = $stock_id;
        if ($stock_id) {
            $this->constant = id_to_constant($stock_id);
        }
    }
}

class StockItemDisplay {
    public $type_label;
    public $constant_label;
    public $id_label;
    public $accel_label;
    public $icon_image;
}
?>