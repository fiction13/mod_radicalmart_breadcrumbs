<?php
/*
 * @package   mod_radicalmart_breadcrumbs
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;
?>
<div aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>" role="navigation">
    <ul class="uk-breadcrumb <?php echo $moduleclass_sfx; ?>" itemscope itemtype="https://schema.org/BreadcrumbList">

		<?php
		// Get rid of duplicated entries on trail including home page when using multilanguage
		for ($i = 0; $i < $count; $i++)
		{
			if ($i === 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link === $list[$i - 1]->link)
			{
				unset($list[$i]);
			}
		}

		// Find last and penultimate items in breadcrumbs list
		end($list);
		$last_item_key = key($list);

		// Make a link if not the last item in the breadcrumbs
		$show_last = $params->get('showLast', 1);

		// Generate the trail
		foreach ($list as $key => $item) :
			if ($key !== $last_item_key) :
				?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php if (!empty($item->link)) : ?>

						<?php if ($item->childs) : ?>
                            <a href="#">
                               <?php echo $item->name; ?> <i uk-icon="chevron-down"></i>
                            </a>

                            <div class="uk-width-medium" uk-dropdown="mode: click">
                                <ul class="uk-nav uk-navbar-dropdown-nav">
                                    <li class="uk-active">
                                        <a itemprop="item" href="<?php echo $item->link; ?>">
                                            <span itemprop="name"><?php echo $item->name; ?></span>
                                        </a>
                                    </li>
                                    <li class="uk-nav-divider"></li>
									<?php foreach ($item->childs as $child) : ?>
                                        <li>
                                            <a href="<?php echo $child->link; ?>"><?php echo $child->title; ?></a>
                                        </li>
									<?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else : ?>
                            <a itemprop="item" href="<?php echo $item->link; ?>" class="pathway">
                                <span itemprop="name"><?php echo $item->name; ?></span>
                            </a>
						<?php endif; ?>

					<?php else : ?>
                        <span itemprop="name">
							<?php echo $item->name; ?>
						</span>
					<?php endif; ?>

                    <meta itemprop="position" content="<?php echo $key + 1; ?>">
                </li>
			<?php elseif ($show_last) :
				// Render last item if reqd. ?>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="active">
					<span itemprop="name">
						<?php echo $item->name; ?>
					</span>
                    <meta itemprop="position" content="<?php echo $key + 1; ?>">
                </li>
			<?php endif;
		endforeach; ?>
    </ul>
</div>
