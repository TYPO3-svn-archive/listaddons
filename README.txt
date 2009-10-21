

Example configuration:

mod.web_list {
	listaddons{
		disable.markAll = 1
		disable.pasteAfterMult = 1
		disable.editInPopup = 1
	}
}

Use those settings to disable some of the new buttons.
If you would like to configure filters for some table/fields, enable this
option using the checkbox in the Extension Manager. Then add a configuration
similar to the following example to your Page TS-Config:

mod.web_list {
	listaddons {
		filters.tx_mytable_xyz {
			myfield_abc = 1
			myfield_xyz = 1
		}
	}
}

Then a filter for the specifieds field of the table "tx_mytable_xyz" will be available in list-view.
When you select a combination of values which doesn't occur on the current page, the complete table-list
will not be visible, as no items could be selected.

Use the new "rewind" button in the top-left corner of the list module to reset all filters.


