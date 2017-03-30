{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{assign var='sBreadcrumb' value=[['name'=>"{s name='AccountTitle'}{/s}", 'link' =>{url controller='account' action='index'}]]}
{/block}

{block name="frontend_index_left_categories_my_account"}{/block}

{* Account Sidebar *}
{block name="frontend_index_left_categories" prepend}
	{block name="frontend_account_sidebar"}
		{include file="frontend/account/sidebar.tpl"}
	{/block}
{/block}

{* Account Main Content *}
{block name="frontend_index_content"}
	<div class="content account--content">

		{* Success messages *}
		{block name="frontend_account_index_success_messages"}
			{include file="frontend/account/success_messages.tpl"}
		{/block}

		{* Error messages *}
		{block name="frontend_account_index_error_messages"}
			{if $sErrorMessages}
				<div class="account--error">
					{include file="frontend/register/error_message.tpl" error_messages=$sErrorMessages}
				</div>
			{/if}
		{/block}

		{* Welcome text *}
		{block name="frontend_account_index_welcome"}
			<div class="account--welcome panel">
				{block name="frontend_account_index_welcome_headline"}
					<h1 class="panel--title">{s name='AccountHeaderWelcome'}{/s},
						{if {config name="displayprofiletitle"}}
							{$sUserData.additional.user.title}
						{/if}
						{$sUserData.additional.user.firstname}
						{$sUserData.additional.user.lastname}
					</h1>
				{/block}

				{block name="frontend_account_index_welcome_content"}
					<div class="panel--body is--wide">
						<p>{s name='AccountHeaderInfo'}{/s}</p>
					</div>
				{/block}
			</div>
		{/block}

		<div data-panel-auto-resizer="true" class="account-info--container">
			{* General user informations *}
			{block name="frontend_account_index_info"}
				<div class="account--info account--box panel has--border is--rounded">

					{block name="frontend_account_index_info_headline"}
						<h2 class="panel--title is--underline">{s name="AccountHeaderBasic"}{/s}</h2>
					{/block}

					{block name="frontend_account_index_info_content"}
						<div class="panel--body is--wide">
							<p>
								{$sUserData.additional.user.salutation|salutation}
								{if {config name="displayprofiletitle"}}
									{$sUserData.additional.user.title}<br/>
								{/if}
								{$sUserData.additional.user.firstname} {$sUserData.additional.user.lastname}<br />
								{if $sUserData.additional.user.birthday}
									{$sUserData.additional.user.birthday|date:'dd.MM.y'}<br />
								{/if}
								{$sUserData.additional.user.email}
							</p>
						</div>
					{/block}

					{block name="frontend_account_index_info_actions"}
						<div class="panel--actions is--wide">
							<a href="{url controller=account action=profile}" title="{s name='AccountLinkChangeProfile'}{/s}" class="btn is--small">
								{s name='AccountLinkChangeProfile'}{/s}
							</a>
						</div>
					{/block}
				</div>
			{/block}

			{* Payment information *}
			{block name="frontend_account_index_payment_method"}
				<div class="account--payment account--box panel has--border is--rounded">

					{block name="frontend_account_index_payment_method_headline"}
						<h2 class="panel--title is--underline">{s name="AccountHeaderPayment"}{/s}</h2>
					{/block}

					{block name="frontend_account_index_payment_method_content"}
						<div class="panel--body is--wide">
							<p>
								<strong>{$sUserData.additional.payment.description}</strong><br />

								{if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
									{s name="AccountInfoInstantDownloads"}{/s}
								{/if}
							</p>
						</div>
					{/block}

					{block name="frontend_account_index_payment_method_actions"}
						{$paymentMethodTitle = {"{s name='AccountLinkChangePayment'}{/s}"|escape}}

						<div class="panel--actions is--wide">
							<a href="{url controller='account' action='payment'}"
							   title="{$paymentMethodTitle|escape}"
							   class="btn is--small">
								{s name='AccountLinkChangePayment'}{/s}
							</a>
						</div>
					{/block}
				</div>
			{/block}
		</div>

		{block name="frontend_account_index_addresses"}
			<div data-panel-auto-resizer="true" class="account-address--container">
				{* Billing addresses *}
				{block name="frontend_account_index_primary_billing"}
					<div class="account--billing account--box panel has--border is--rounded">

						{block name="frontend_account_index_primary_billing_headline"}
							<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryBilling"}{/s}</h2>
						{/block}

						{block name="frontend_account_index_primary_billing_content"}
							<div class="panel--body is--wide">
								{if $sUserData.billingaddress.company}
									<p>
										<span class="address--company">{$sUserData.billingaddress.company}</span>{if $sUserData.billingaddress.department} - <span class="address--department">{$sUserData.billingaddress.department}</span>{/if}
									</p>
								{/if}
								<p>
									<span class="address--salutation">{$sUserData.billingaddress.salutation|salutation}</span>
									{if {config name="displayprofiletitle"}}
										<span class="address--title">{$sUserData.billingaddress.title}</span><br/>
									{/if}
									<span class="address--firstname">{$sUserData.billingaddress.firstname}</span> <span class="address--lastname">{$sUserData.billingaddress.lastname}</span><br />
									<span class="address--street">{$sUserData.billingaddress.street}</span><br />
									{if $sUserData.billingaddress.additional_address_line1}<span class="address--additional-one">{$sUserData.billingaddress.additional_address_line1}</span><br />{/if}
									{if $sUserData.billingaddress.additional_address_line2}<span class="address--additional-two">{$sUserData.billingaddress.additional_address_line2}</span><br />{/if}
									{if {config name=showZipBeforeCity}}
                                        <span class="address--zipcode">{$sUserData.billingaddress.zipcode}</span> <span class="address--city">{$sUserData.billingaddress.city}</span>
                                    {else}
                                        <span class="address--city">{$sUserData.billingaddress.city}</span> <span class="address--zipcode">{$sUserData.billingaddress.zipcode}</span>
                                    {/if}<br />
									{if $sUserData.additional.state.statename}<span class="address--statename">{$sUserData.additional.state.statename}</span><br />{/if}
                                    <span class="address--countryname">{$sUserData.additional.country.countryname}</span>
								</p>
							</div>
						{/block}

						{block name="frontend_account_index_primary_billing_actions"}
							<div class="panel--actions is--wide">
								<a href="{url controller=address action=edit id=$sUserData.additional.user.default_billing_address_id sTarget=account}"
								   title="{s name='AccountLinkChangeBilling'}{/s}"
								   class="btn">
									{s name="AccountLinkChangeBilling"}{/s}
								</a>
								<br/>
								<a href="{url controller=address}"
								   data-address-selection="true"
								   data-setDefaultBillingAddress="1"
								   data-id="{$sUserData.additional.user.default_billing_address_id}"
								   title="{s name='AccountLinkChangeBilling'}{/s}">
									{s name="AccountLinkSelectBilling"}{/s}
								</a>
							</div>
						{/block}
					</div>
				{/block}

				{* Shipping addresses *}
				{block name="frontend_account_index_primary_shipping"}
					<div class="account--shipping account--box panel has--border is--rounded">

						{block name="frontend_account_index_primary_shipping_headline"}
							<h2 class="panel--title is--underline">{s name="AccountHeaderPrimaryShipping"}{/s}</h2>
						{/block}

						{block name="frontend_account_index_primary_shipping_content"}
							<div class="panel--body is--wide">
								{if $sUserData.shippingaddress.company}
									<p>
										<span class="address--company">{$sUserData.shippingaddress.company}</span>{if $sUserData.shippingaddress.department} - <span class="address--department">{$sUserData.shippingaddress.department}</span>{/if}
									</p>
								{/if}
								<p>
									<span class="address--salutation">{$sUserData.shippingaddress.salutation|salutation}</span>
									{if {config name="displayprofiletitle"}}
                                        <span class="address--title">{$sUserData.shippingaddress.title}</span><br/>
									{/if}
                                    <span class="address--firstname">{$sUserData.shippingaddress.firstname}</span> <span class="address--lastname">{$sUserData.shippingaddress.lastname}</span><br />
									<span class="address--street">{$sUserData.shippingaddress.street}</span><br />
									{if $sUserData.shippingaddress.additional_address_line1}<span class="address--additional-one">{$sUserData.shippingaddress.additional_address_line1}</span><br />{/if}
									{if $sUserData.shippingaddress.additional_address_line2}<span class="address--additional-two">{$sUserData.shippingaddress.additional_address_line2}</span><br />{/if}
									{if {config name=showZipBeforeCity}}
                                        <span class="address--zipcode">{$sUserData.shippingaddress.zipcode}</span> <span class="address--city">{$sUserData.shippingaddress.city}</span>
                                    {else}
                                        <span class="address--city">{$sUserData.shippingaddress.city}</span> <span class="address--zipcode">{$sUserData.shippingaddress.zipcode}</span>
                                    {/if}<br />
									{if $sUserData.additional.stateShipping.statename}<span class="address--statename">{$sUserData.additional.stateShipping.statename}</span><br />{/if}
                                    <span class="address--countryname">{$sUserData.additional.countryShipping.countryname}</span>
								</p>
							</div>
						{/block}

						{block name="frontend_account_index_primary_shipping_actions"}
							<div class="panel--actions is--wide">
								<a href="{url controller=address action=edit id=$sUserData.additional.user.default_shipping_address_id sTarget=account}"
								   title="{s name='AccountLinkChangeBilling'}{/s}"
								   class="btn">
									{s name="AccountLinkChangeShipping"}{/s}
								</a>
								<br/>
								<a href="{url controller=address}"
								   data-address-selection="true"
								   data-setDefaultShippingAddress="1"
								   data-id="{$sUserData.additional.user.default_shipping_address_id}"
								   title="{s name='AccountLinkChangeBilling'}{/s}">
									{s name="AccountLinkSelectBilling"}{/s}
								</a>
							</div>
						{/block}
					</div>
				{/block}
			</div>
		{/block}

        {* Newsletter settings *}
        {block name="frontend_account_index_newsletter_settings"}
            <div class="account--newsletter account--box panel has--border is--rounded newsletter">

                {block name="frontend_account_index_newsletter_settings_headline"}
                <h2 class="panel--title is--underline">{s name="AccountHeaderNewsletter"}{/s}</h2>
                {/block}

                {block name="frontend_account_index_newsletter_settings_content"}
                    <div class="panel--body is--wide">
                        <form name="frmRegister" method="post" action="{url action=saveNewsletter}">
                            <fieldset>
                                <input type="checkbox" name="newsletter" value="1" id="newsletter" data-auto-submit="true" {if $sUserData.additional.user.newsletter}checked="checked"{/if} />
                                <label for="newsletter">
                                    {s name="AccountLabelWantNewsletter"}{/s}
                                </label>
                            </fieldset>
                        </form>
                    </div>
                {/block}
            </div>
		{/block}

	</div>
{/block}