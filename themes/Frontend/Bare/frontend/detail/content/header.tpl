{block name='frontend_detail_index_header'}
    <header class="product--header">
        {block name='frontend_detail_index_header_inner'}
            <div class="product--info">
                {block name='frontend_detail_index_product_info'}

                    {* Product name *}
                    {block name='frontend_detail_index_name'}
                        <h1 class="product--title" itemprop="name">
                            {$sArticle.articleName}
                        </h1>
                    {/block}

                    {block name="frontend_detail_index_data_image"}
                        {foreach $sArticle.images as $image}
                            <meta itemprop="image" content="{$image.source}"/>
                        {/foreach}
                    {/block}

                    {block name="frontend_detail_index_data_ean"}
                        {if $sArticle.ean}
                            {$eanLength = $sArticle.ean|strlen}
                            {if $eanLength == 8}
                                <meta itemprop="gtin8" content="{$sArticle.ean}"/>
                            {elseif $eanLength == 12}
                                <meta itemprop="gtin12" content="{$sArticle.ean}"/>
                            {elseif $eanLength == 13}
                                <meta itemprop="gtin13" content="{$sArticle.ean}"/>
                            {elseif $eanLength == 14}
                                <meta itemprop="gtin14" content="{$sArticle.ean}"/>
                            {/if}
                        {/if}
                    {/block}

                    {* Product - Supplier information *}
                    {block name='frontend_detail_supplier_info'}
                        {if $sArticle.supplierImg}
                            <div class="product--supplier">
                                {s name="DetailDescriptionLinkInformation" namespace="frontend/detail/description" assign="snippetDetailDescriptionLinkInformation"}{/s}
                                <a href="{url controller='listing' action='manufacturer' sSupplier=$sArticle.supplierID}"
                                   title="{$snippetDetailDescriptionLinkInformation|escape}"
                                   class="product--supplier-link">
                                    <img src="{$sArticle.supplierImg}" alt="{$sArticle.supplierName|escape}">
                                </a>
                            </div>
                        {/if}
                    {/block}

                    {* Product rating *}
                    {block name="frontend_detail_comments_overview"}
                        {if !{config name=VoteDisable}}
                            <div class="product--rating-container">
                                {s namespace="frontend/detail/actions" name="DetailLinkReview" assign="snippetDetailLinkReview"}{/s}
                                <a href="#product--publish-comment" class="product--rating-link" rel="nofollow" title="{$snippetDetailLinkReview|escape}">
                                    {include file='frontend/_includes/rating.tpl' points=$sArticle.sVoteAverage.average type="aggregated" count=$sArticle.sVoteAverage.count}
                                </a>
                            </div>
                        {/if}
                    {/block}
                {/block}
            </div>
        {/block}
    </header>
{/block}
