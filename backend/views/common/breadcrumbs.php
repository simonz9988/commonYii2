<?if($this->params['breadcrumbs']):?>
	<div class="col-md-8 col-xs-12">
		<ul class="breadcrumb ">			    					
			<?foreach($this->params['breadcrumbs'] as $b):?>
				<li <?if($b['active']):?>class="active"<?endif;?>><a href="<?if($b['url']):?><?=$b['url']?><?else:?>javascript:void(0);<?endif?>"><?=$b['name']?></a></li>
			<?endforeach;?>
		</ul>
	</div>
<?endif;?>