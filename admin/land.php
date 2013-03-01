<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *                                   ATTENTION!
 * If you see this message in browser (Internet Explorer, Mozilla firefox, etc.)
 * this means that
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */


    require_once 'components/utils/check_utils.php';
    CheckPHPVersion();



    require_once 'database_engine/mysql_engine.php';
    require_once 'components/page.php';
    require_once 'phpgen_settings.php';
    require_once 'authorization.php';

    function GetConnectionOptions()
    {
        $result = GetGlobalConnectionOptions();
        $result['client_encoding'] = 'utf8';
        GetApplication()->GetUserAuthorizationStrategy()->ApplyIdentityToConnectionOptions($result);
        return $result;
    }

    
    ?><?php
    
    ?><?php
    
    class landPage extends Page
    {
        protected function DoBeforeCreate()
        {
            $this->dataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land`');
            $field = new IntegerField('id', null, null, true);
            $this->dataset->AddField($field, true);
            $field = new IntegerField('x');
            $this->dataset->AddField($field, true);
            $field = new IntegerField('y');
            $this->dataset->AddField($field, true);
            $field = new IntegerField('land_special_id');
            $this->dataset->AddField($field, false);
            $field = new IntegerField('owner_user_id');
            $this->dataset->AddField($field, false);
            $field = new StringField('title');
            $this->dataset->AddField($field, false);
            $field = new StringField('detail');
            $this->dataset->AddField($field, false);
            $field = new BlobField('picture');
            $this->dataset->AddField($field, false);
            $this->dataset->AddLookupField('land_special_id', 'land_special', new IntegerField('id', null, null, true), new IntegerField('owner_user_id', 'land_special_id_owner_user_id', 'land_special_id_owner_user_id_land_special'), 'land_special_id_owner_user_id_land_special');
            $this->dataset->AddLookupField('owner_user_id', 'user', new IntegerField('id', null, null, true), new StringField('first_name', 'owner_user_id_first_name', 'owner_user_id_first_name_user'), 'owner_user_id_first_name_user');
        }
    
        protected function CreatePageNavigator()
        {
            $result = new CompositePageNavigator($this);
            
            $partitionNavigator = new PageNavigator('pnav', $this, $this->dataset);
            $partitionNavigator->SetRowsPerPage(20);
            $result->AddPageNavigator($partitionNavigator);
            
            return $result;
        }
    
        public function GetPageList()
        {
            $currentPageCaption = $this->GetShortCaption();
            $result = new PageList();
            if (GetCurrentUserGrantForDataSource('land')->HasViewGrant())
                $result->AddPage(new PageLink($this->RenderText('Land'), 'land.php', $this->RenderText('Land'), $currentPageCaption == $this->RenderText('Land')));
            if (GetCurrentUserGrantForDataSource('land_special')->HasViewGrant())
                $result->AddPage(new PageLink($this->RenderText('Land Special'), 'land_special.php', $this->RenderText('Land Special'), $currentPageCaption == $this->RenderText('Land Special')));
            if (GetCurrentUserGrantForDataSource('news')->HasViewGrant())
                $result->AddPage(new PageLink($this->RenderText('News'), 'news.php', $this->RenderText('News'), $currentPageCaption == $this->RenderText('News')));
            if (GetCurrentUserGrantForDataSource('user')->HasViewGrant())
                $result->AddPage(new PageLink($this->RenderText('User'), 'user.php', $this->RenderText('User'), $currentPageCaption == $this->RenderText('User')));
            if (GetCurrentUserGrantForDataSource('settings')->HasViewGrant())
                $result->AddPage(new PageLink($this->RenderText('Settings'), 'settings.php', $this->RenderText('Settings'), $currentPageCaption == $this->RenderText('Settings')));
            
            if ( HasAdminPage() && GetApplication()->HasAdminGrantForCurrentUser() )
              $result->AddPage(new PageLink($this->RenderText('Admin page'), 'phpgen_admin.php', 'Admin page', false, true));
            return $result;
        }
    
        protected function CreateRssGenerator()
        {
            return null;
        }
    
        protected function CreateGridSearchControl($grid)
        {
            $grid->UseFilter = true;
            $grid->SearchControl = new SimpleSearch('landssearch', $this->dataset,
                array('id', 'x', 'y', 'land_special_id_owner_user_id', 'owner_user_id_first_name', 'title', 'detail'),
                array($this->RenderText('Id'), $this->RenderText('X'), $this->RenderText('Y'), $this->RenderText('Land Special Id'), $this->RenderText('Owner User Id'), $this->RenderText('Title'), $this->RenderText('Detail')),
                array(
                    '=' => $this->GetLocalizerCaptions()->GetMessageString('equals'),
                    '<>' => $this->GetLocalizerCaptions()->GetMessageString('doesNotEquals'),
                    '<' => $this->GetLocalizerCaptions()->GetMessageString('isLessThan'),
                    '<=' => $this->GetLocalizerCaptions()->GetMessageString('isLessThanOrEqualsTo'),
                    '>' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThan'),
                    '>=' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThanOrEqualsTo'),
                    'ILIKE' => $this->GetLocalizerCaptions()->GetMessageString('Like'),
                    'STARTS' => $this->GetLocalizerCaptions()->GetMessageString('StartsWith'),
                    'ENDS' => $this->GetLocalizerCaptions()->GetMessageString('EndsWith'),
                    'CONTAINS' => $this->GetLocalizerCaptions()->GetMessageString('Contains')
                    ), $this->GetLocalizerCaptions(), $this, 'CONTAINS'
                );
        }
    
        protected function CreateGridAdvancedSearchControl($grid)
        {
            $this->AdvancedSearchControl = new AdvancedSearchControl('landasearch', $this->dataset, $this->GetLocalizerCaptions(), $this->GetColumnVariableContainer(), $this->CreateLinkBuilder());
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('id', $this->RenderText('Id')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('x', $this->RenderText('X')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('y', $this->RenderText('Y')));
            
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land_special`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new IntegerField('owner_user_id');
            $lookupDataset->AddField($field, false);
            $field = new StringField('title');
            $lookupDataset->AddField($field, false);
            $field = new StringField('detail');
            $lookupDataset->AddField($field, false);
            $field = new BlobField('picture');
            $lookupDataset->AddField($field, false);
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateLookupSearchInput('land_special_id', $this->RenderText('Land Special Id'), $lookupDataset, 'id', 'owner_user_id', false));
            
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`user`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('first_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('last_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('email');
            $lookupDataset->AddField($field, true);
            $field = new StringField('password');
            $lookupDataset->AddField($field, false);
            $field = new StringField('city');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_us');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_nonus');
            $lookupDataset->AddField($field, false);
            $field = new StringField('country');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('is_admin');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_own_land');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_important_places');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_grid');
            $lookupDataset->AddField($field, false);
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateLookupSearchInput('owner_user_id', $this->RenderText('Owner User Id'), $lookupDataset, 'id', 'first_name', false));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('title', $this->RenderText('Title')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('detail', $this->RenderText('Detail')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateBlobSearchInput('picture', $this->RenderText('Picture')));
        }
    
        protected function AddOperationsColumns($grid)
        {
            $actionsBandName = 'actions';
            $grid->AddBandToBegin($actionsBandName, $this->GetLocalizerCaptions()->GetMessageString('Actions'), true);
            if ($this->GetSecurityInfo()->HasViewGrant())
            {
                $column = $grid->AddViewColumn(new RowOperationByLinkColumn($this->GetLocalizerCaptions()->GetMessageString('View'), OPERATION_VIEW, $this->dataset), $actionsBandName);
                $column->SetImagePath('images/view_action.png');
            }
            if ($this->GetSecurityInfo()->HasEditGrant())
            {
                $column = $grid->AddViewColumn(new RowOperationByLinkColumn($this->GetLocalizerCaptions()->GetMessageString('Edit'), OPERATION_EDIT, $this->dataset), $actionsBandName);
                $column->SetImagePath('images/edit_action.png');
                $column->OnShow->AddListener('ShowEditButtonHandler', $this);
            }
            if ($this->GetSecurityInfo()->HasDeleteGrant())
            {
                $column = $grid->AddViewColumn(new RowOperationByLinkColumn($this->GetLocalizerCaptions()->GetMessageString('Delete'), OPERATION_DELETE, $this->dataset), $actionsBandName);
                $column->SetImagePath('images/delete_action.png');
                $column->OnShow->AddListener('ShowDeleteButtonHandler', $this);
            $column->SetAdditionalAttribute("modal-delete", "true");
            $column->SetAdditionalAttribute("delete-handler-name", $this->GetModalGridDeleteHandler());
            }
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $column = $grid->AddViewColumn(new RowOperationByLinkColumn($this->GetLocalizerCaptions()->GetMessageString('Copy'), OPERATION_COPY, $this->dataset), $actionsBandName);
                $column->SetImagePath('images/copy_action.png');
            }
        }
    
        protected function AddFieldColumns($grid)
        {
            //
            // View column for id field
            //
            $column = new TextViewColumn('id', 'Id', $this->dataset);
            $column->SetOrderable(true);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for x field
            //
            $column = new TextViewColumn('x', 'X', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for x field
            //
            $editor = new TextEdit('x_edit');
            $editColumn = new CustomEditColumn('X', 'x', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for x field
            //
            $editor = new TextEdit('x_edit');
            $editColumn = new CustomEditColumn('X', 'x', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for y field
            //
            $column = new TextViewColumn('y', 'Y', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for y field
            //
            $editor = new TextEdit('y_edit');
            $editColumn = new CustomEditColumn('Y', 'y', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for y field
            //
            $editor = new TextEdit('y_edit');
            $editColumn = new CustomEditColumn('Y', 'y', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for owner_user_id field
            //
            $column = new TextViewColumn('land_special_id_owner_user_id', 'Land Special Id', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for land_special_id field
            //
            $editor = new ComboBox('land_special_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land_special`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new IntegerField('owner_user_id');
            $lookupDataset->AddField($field, false);
            $field = new StringField('title');
            $lookupDataset->AddField($field, false);
            $field = new StringField('detail');
            $lookupDataset->AddField($field, false);
            $field = new BlobField('picture');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('owner_user_id', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Land Special Id', 
                'land_special_id', 
                $editor, 
                $this->dataset, 'id', 'owner_user_id', $lookupDataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for land_special_id field
            //
            $editor = new ComboBox('land_special_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land_special`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new IntegerField('owner_user_id');
            $lookupDataset->AddField($field, false);
            $field = new StringField('title');
            $lookupDataset->AddField($field, false);
            $field = new StringField('detail');
            $lookupDataset->AddField($field, false);
            $field = new BlobField('picture');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('owner_user_id', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Land Special Id', 
                'land_special_id', 
                $editor, 
                $this->dataset, 'id', 'owner_user_id', $lookupDataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for first_name field
            //
            $column = new TextViewColumn('owner_user_id_first_name', 'Owner User Id', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for owner_user_id field
            //
            $editor = new ComboBox('owner_user_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`user`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('first_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('last_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('email');
            $lookupDataset->AddField($field, true);
            $field = new StringField('password');
            $lookupDataset->AddField($field, false);
            $field = new StringField('city');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_us');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_nonus');
            $lookupDataset->AddField($field, false);
            $field = new StringField('country');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('is_admin');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_own_land');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_important_places');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_grid');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('first_name', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Owner User Id', 
                'owner_user_id', 
                $editor, 
                $this->dataset, 'id', 'first_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for owner_user_id field
            //
            $editor = new ComboBox('owner_user_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`user`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('first_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('last_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('email');
            $lookupDataset->AddField($field, true);
            $field = new StringField('password');
            $lookupDataset->AddField($field, false);
            $field = new StringField('city');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_us');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_nonus');
            $lookupDataset->AddField($field, false);
            $field = new StringField('country');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('is_admin');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_own_land');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_important_places');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_grid');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('first_name', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Owner User Id', 
                'owner_user_id', 
                $editor, 
                $this->dataset, 'id', 'first_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for title field
            //
            $column = new TextViewColumn('title', 'Title', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for title field
            //
            $editor = new TextEdit('title_edit');
            $editor->SetSize(50);
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Title', 'title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for title field
            //
            $editor = new TextEdit('title_edit');
            $editor->SetSize(50);
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Title', 'title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('detail_handler');
            
            /* <inline edit column> */
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for picture field
            //
            $column = new DownloadDataColumn('picture', 'Picture', $this->dataset, '<img alt="download" src="' . 'images/download.gif' . '"><br>download');
            
            /* <inline edit column> */
            //
            // Edit column for picture field
            //
            $editor = new ImageUploader('picture_edit');
            $editor->SetShowImage(false);
            $editColumn = new FileUploadingColumn('Picture', 'picture', $editor, $this->dataset, false, false, 'picture_handler_inline_edit');
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for picture field
            //
            $editor = new ImageUploader('picture_edit');
            $editor->SetShowImage(false);
            $editColumn = new FileUploadingColumn('Picture', 'picture', $editor, $this->dataset, false, false, 'picture_handler_inline_insert');
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
        }
    
        protected function AddSingleRecordViewColumns($grid)
        {
            //
            // View column for id field
            //
            $column = new TextViewColumn('id', 'Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for x field
            //
            $column = new TextViewColumn('x', 'X', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for y field
            //
            $column = new TextViewColumn('y', 'Y', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for owner_user_id field
            //
            $column = new TextViewColumn('land_special_id_owner_user_id', 'Land Special Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for first_name field
            //
            $column = new TextViewColumn('owner_user_id_first_name', 'Owner User Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for title field
            //
            $column = new TextViewColumn('title', 'Title', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('detail_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for picture field
            //
            $column = new DownloadDataColumn('picture', 'Picture', $this->dataset, '<img alt="download" src="' . 'images/download.gif' . '"><br>download');
            $grid->AddSingleRecordViewColumn($column);
        }
    
        protected function AddEditColumns($grid)
        {
            //
            // Edit column for x field
            //
            $editor = new TextEdit('x_edit');
            $editColumn = new CustomEditColumn('X', 'x', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for y field
            //
            $editor = new TextEdit('y_edit');
            $editColumn = new CustomEditColumn('Y', 'y', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for land_special_id field
            //
            $editor = new ComboBox('land_special_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land_special`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new IntegerField('owner_user_id');
            $lookupDataset->AddField($field, false);
            $field = new StringField('title');
            $lookupDataset->AddField($field, false);
            $field = new StringField('detail');
            $lookupDataset->AddField($field, false);
            $field = new BlobField('picture');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('owner_user_id', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Land Special Id', 
                'land_special_id', 
                $editor, 
                $this->dataset, 'id', 'owner_user_id', $lookupDataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for owner_user_id field
            //
            $editor = new ComboBox('owner_user_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`user`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('first_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('last_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('email');
            $lookupDataset->AddField($field, true);
            $field = new StringField('password');
            $lookupDataset->AddField($field, false);
            $field = new StringField('city');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_us');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_nonus');
            $lookupDataset->AddField($field, false);
            $field = new StringField('country');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('is_admin');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_own_land');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_important_places');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_grid');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('first_name', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Owner User Id', 
                'owner_user_id', 
                $editor, 
                $this->dataset, 'id', 'first_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for title field
            //
            $editor = new TextEdit('title_edit');
            $editor->SetSize(50);
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Title', 'title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for picture field
            //
            $editor = new ImageUploader('picture_edit');
            $editor->SetShowImage(false);
            $editColumn = new FileUploadingColumn('Picture', 'picture', $editor, $this->dataset, false, false, 'picture_handler_edit');
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
        }
    
        protected function AddInsertColumns($grid)
        {
            //
            // Edit column for x field
            //
            $editor = new TextEdit('x_edit');
            $editColumn = new CustomEditColumn('X', 'x', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for y field
            //
            $editor = new TextEdit('y_edit');
            $editColumn = new CustomEditColumn('Y', 'y', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for land_special_id field
            //
            $editor = new ComboBox('land_special_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`land_special`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new IntegerField('owner_user_id');
            $lookupDataset->AddField($field, false);
            $field = new StringField('title');
            $lookupDataset->AddField($field, false);
            $field = new StringField('detail');
            $lookupDataset->AddField($field, false);
            $field = new BlobField('picture');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('owner_user_id', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Land Special Id', 
                'land_special_id', 
                $editor, 
                $this->dataset, 'id', 'owner_user_id', $lookupDataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for owner_user_id field
            //
            $editor = new ComboBox('owner_user_id_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`user`');
            $field = new IntegerField('id', null, null, true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('first_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('last_name');
            $lookupDataset->AddField($field, false);
            $field = new StringField('email');
            $lookupDataset->AddField($field, true);
            $field = new StringField('password');
            $lookupDataset->AddField($field, false);
            $field = new StringField('city');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_us');
            $lookupDataset->AddField($field, false);
            $field = new StringField('state_nonus');
            $lookupDataset->AddField($field, false);
            $field = new StringField('country');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('is_admin');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_own_land');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_important_places');
            $lookupDataset->AddField($field, false);
            $field = new IntegerField('pref_show_grid');
            $lookupDataset->AddField($field, false);
            $lookupDataset->SetOrderBy('first_name', GetOrderTypeAsSQL(otAscending));
            $editColumn = new LookUpEditColumn(
                'Owner User Id', 
                'owner_user_id', 
                $editor, 
                $this->dataset, 'id', 'first_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for title field
            //
            $editor = new TextEdit('title_edit');
            $editor->SetSize(50);
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Title', 'title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for picture field
            //
            $editor = new ImageUploader('picture_edit');
            $editor->SetShowImage(false);
            $editColumn = new FileUploadingColumn('Picture', 'picture', $editor, $this->dataset, false, false, 'picture_handler_insert');
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $grid->SetShowAddButton(true);
                $grid->SetShowInlineAddButton(false);
            }
            else
            {
                $grid->SetShowInlineAddButton(false);
                $grid->SetShowAddButton(false);
            }
        }
    
        protected function AddPrintColumns($grid)
        {
            //
            // View column for id field
            //
            $column = new TextViewColumn('id', 'Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for x field
            //
            $column = new TextViewColumn('x', 'X', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for y field
            //
            $column = new TextViewColumn('y', 'Y', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for owner_user_id field
            //
            $column = new TextViewColumn('land_special_id_owner_user_id', 'Land Special Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for first_name field
            //
            $column = new TextViewColumn('owner_user_id_first_name', 'Owner User Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for title field
            //
            $column = new TextViewColumn('title', 'Title', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for picture field
            //
            $column = new DownloadDataColumn('picture', 'Picture', $this->dataset, '<img alt="download" src="' . 'images/download.gif' . '"><br>download');
            $grid->AddPrintColumn($column);
        }
    
        protected function AddExportColumns($grid)
        {
            //
            // View column for id field
            //
            $column = new TextViewColumn('id', 'Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for x field
            //
            $column = new TextViewColumn('x', 'X', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for y field
            //
            $column = new TextViewColumn('y', 'Y', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for owner_user_id field
            //
            $column = new TextViewColumn('land_special_id_owner_user_id', 'Land Special Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for first_name field
            //
            $column = new TextViewColumn('owner_user_id_first_name', 'Owner User Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for title field
            //
            $column = new TextViewColumn('title', 'Title', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for picture field
            //
            $column = new DownloadDataColumn('picture', 'Picture', $this->dataset, '<img alt="download" src="' . 'images/download.gif' . '"><br>download');
            $grid->AddExportColumn($column);
        }
    
        public function GetPageDirection()
        {
            return null;
        }
    
        protected function ApplyCommonColumnEditProperties($column)
        {
            $column->SetShowSetToNullCheckBox(true);
    	$column->SetVariableContainer($this->GetColumnVariableContainer());
        }
    
        function GetCustomClientScript()
        {
            return ;
        }
        
        function GetOnPageLoadedClientScript()
        {
            return ;
        }
        public function ShowEditButtonHandler($show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasEditGrant($this->GetDataset());
        }
        public function ShowDeleteButtonHandler($show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasDeleteGrant($this->GetDataset());
        }
        
        public function GetModalGridDeleteHandler() { return 'land_modal_delete'; }
        protected function GetEnableModalGridDelete() { return true; }
    
        protected function CreateGrid()
        {
            $result = new Grid($this, $this->dataset, 'landGrid');
            if ($this->GetSecurityInfo()->HasDeleteGrant())
               $result->SetAllowDeleteSelected(true);
            else
               $result->SetAllowDeleteSelected(false);   
            
            ApplyCommonPageSettings($this, $result);
            
            $result->SetUseImagesForActions(true);
            $result->SetUseFixedHeader(false);
            
            $result->SetShowLineNumbers(false);
            
            $result->SetHighlightRowAtHover(false);
            $result->SetWidth('');
            $this->CreateGridSearchControl($result);
            $this->CreateGridAdvancedSearchControl($result);
            $this->AddOperationsColumns($result);
            $this->AddFieldColumns($result);
            $this->AddSingleRecordViewColumns($result);
            $this->AddEditColumns($result);
            $this->AddInsertColumns($result);
            $this->AddPrintColumns($result);
            $this->AddExportColumns($result);
    
            $this->SetShowPageList(true);
            $this->SetExportToExcelAvailable(true);
            $this->SetExportToWordAvailable(true);
            $this->SetExportToXmlAvailable(true);
            $this->SetExportToCsvAvailable(true);
            $this->SetExportToPdfAvailable(true);
            $this->SetPrinterFriendlyAvailable(true);
            $this->SetSimpleSearchAvailable(true);
            $this->SetAdvancedSearchAvailable(true);
            $this->SetVisualEffectsEnabled(true);
            $this->SetShowTopPageNavigator(true);
            $this->SetShowBottomPageNavigator(true);
    
            //
            // Http Handlers
            //
            //
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for detail field
            //
            $editor = new TextAreaEdit('detail_edit', 50, 8);
            $editColumn = new CustomEditColumn('Detail', 'detail', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'detail_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            $handler = new DownloadHTTPHandler($this->dataset, 'picture', 'picture_handler', '', '');
            GetApplication()->RegisterHTTPHandler($handler);
            
            $handler = new ImageHTTPHandler($this->dataset, 'picture', 'picture_handler_inline_edit', new NullFilter());
            GetApplication()->RegisterHTTPHandler($handler);
            
            $handler = new ImageHTTPHandler($this->dataset, 'picture', 'picture_handler_inline_insert', new NullFilter());
            GetApplication()->RegisterHTTPHandler($handler);//
            // View column for detail field
            //
            $column = new TextViewColumn('detail', 'Detail', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'detail_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            $handler = new DownloadHTTPHandler($this->dataset, 'picture', 'picture_handler', '', '');
            GetApplication()->RegisterHTTPHandler($handler);
            $handler = new ImageHTTPHandler($this->dataset, 'picture', 'picture_handler_edit', new NullFilter());
            GetApplication()->RegisterHTTPHandler($handler);
            $handler = new ImageHTTPHandler($this->dataset, 'picture', 'picture_handler_insert', new NullFilter());
            GetApplication()->RegisterHTTPHandler($handler);
            $handler = new DownloadHTTPHandler($this->dataset, 'picture', 'picture_handler', '', '');
            GetApplication()->RegisterHTTPHandler($handler);
            return $result;
        }
        
        protected function OpenAdvancedSearchByDefault()
        {
            return false;
        }
    
        protected function DoGetGridHeader()
        {
            return '';
        }
    }

    SetUpUserAuthorization(GetApplication());

    try
    {
        $Page = new landPage("land.php", "land", GetCurrentUserGrantForDataSource("land"), 'UTF-8');
        $Page->SetShortCaption('Land');
        $Page->SetHeader(GetPagesHeader());
        $Page->SetFooter(GetPagesFooter());
        $Page->SetCaption('Land');
        $Page->SetRecordPermission(GetCurrentUserRecordPermissionsForDataSource("land"));

        GetApplication()->SetMainPage($Page);
        GetApplication()->Run();
    }
    catch(Exception $e)
    {
        ShowErrorPage($e->getMessage());
    }

?>
