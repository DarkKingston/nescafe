(function ($, Drupal, settings) {
  'use strict';

  Drupal.theme.ln_tint_post_author = function (post) {
    if(post.attributes.author){
      let picture = settings.ln_tint_connector.default_avatar;
      if(post.attributes.author.image_url){
        picture = post.attributes.author.image_url;
      }
      return `
        <img src="${picture}" alt="${Drupal.t('Author picture')}" />
        <span class="tint-author-name">${post.attributes.author.username ?? ''}</span>
    `;
    }
  };

  Drupal.theme.ln_tint_post_image = function (post) {
    return `<img class="tint-post-image" src="${post.attributes.image_url}" />`;
  };

  Drupal.theme.ln_tint_post_media = function (post) {
    if(post.attributes.video_url){
      return `<video loop preload="none">
          <source src="${post.attributes.video_url}">
        </video>`;
    }
    return Drupal.theme('ln_tint_post_image', post);
  };

  Drupal.theme.ln_tint_post_modal = function (post) {
    let author = Drupal.theme('ln_tint_post_author', post);
    let media = Drupal.theme('ln_tint_post_media', post);

    return `<div class="tint-post-info row g-0 no-gutters ln-type-${post.attributes.type}">
        <div class="col-sm-6 tint-media">
            ${media}
        </div>
        <div class="col-sm-6 tint-content">
            <div class="tint-content-wrapper">
              <div class="tint-author">
                  ${author}
              </div>
              <div class="tint-text">
                  <a href="${post.attributes.url}" target="_blank">
                      ${post.attributes.text}
                  </a>
              </div>
              <div class="tint-time">
                  ${Drupal.ln_tint_connector.parse_date(post.attributes.published_at)}
              </div>
            </div>
        </div>
    </div>`;
  };

  Drupal.theme.ln_tint_post = function (post) {
    let modal = Drupal.theme('ln_tint_post_modal', post);
    let image = Drupal.theme('ln_tint_post_image', post);

    return `<div class="tint-post ln-type-${post.attributes.type} ${post.attributes.video_url ? 'ln-media-video' : 'ln-media-image'} col-3">
          <div class="tint-post-wrapper">
            <a href="#" class="tint-post-modal-link">
                ${image}
            </a>
          </div>
          <div class="modal-data" style="display:none">
              ${modal}
          </div>
    </div>`;
  };

  Drupal.theme.ln_tint_posts = function () {
    return `<div class="tint-social ln-tint-post-list row g-2 g-sm-4"></div>`;
  }

  Drupal.theme.ln_tint_links = function (links, show_next, show_prev) {
    let next = '';
    if(show_next){
      next = `<li class="page-item"><a href="${links.next}" class="ln-tint-link ln-tint-next page-link">${Drupal.t('Next &raquo;')}</a></li>`;
    }
    else {
      next = `<li class="page-item disabled"><a class="ln-tint-link ln-tint-next page-link">${Drupal.t('Next &raquo;')}</a></li>`;
    }
    let prev = '';
    if(show_prev){
      prev = `<li class="page-item"><a href="${links.previous}" class="ln-tint-link ln-tint-prev page-link">${Drupal.t('&laquo; Previous')}</a></li>`;
    }
    else {
      prev = `<li class="page-item disabled"><a class="ln-tint-link ln-tint-prev page-link">${Drupal.t('&laquo; Previous')}</a></li>`;
    }
    return `<nav aria-label="${Drupal.t('Page navigation')}" class="ln-tint-pagination mt-4 pager">
      <ul class="pagination justify-content-center">
        ${prev}
        ${next}
      </ul>
    </nav>`;
  }

  Drupal.ln_tint_connector = {};

  Drupal.ln_tint_connector.parse_date = function(date) {
    let system_date = new Date(Date.parse(date));
    let user_date = new Date();
    let diff = Math.floor((user_date - system_date) / 1000);

    if (diff <= 1) {
      return Drupal.t('just now');
    }
    if (diff < 20) {
      return Drupal.t('@time seconds ago', {
        '@time': diff
      });
    }
    if (diff < 40) {
      return Drupal.t('half a minute ago');
    }
    if (diff < 60) {
      return Drupal.t('less than a minute ago');
    }
    if (diff <= 90) {
      return Drupal.t('one minute ago');
    }
    if (diff <= 3540) {
      return Drupal.t('@time minutes ago', {
        '@time': Math.round(diff / 60)
      });
    }
    if (diff <= 5400) {
      return Drupal.t('1 hour ago');
    }
    if (diff <= 86400) {
      return Drupal.t('@time hours ago', {
        '@time': Math.round(diff / 3600)
      });
    }
    if (diff <= 129600) {
      return Drupal.t('1 day ago');
    }
    if (diff < 604800) {
      return Drupal.t('@time days ago', {
        '@time': Math.round(diff / 86400)
      });
    }
    if (diff <= 777600) {
      return Drupal.t('1 week ago');
    }
    return Drupal.t('on @time', {
      '@time': system_date.toLocaleString()
    });
  };

  Drupal.ln_tint_connector.get_posts = function($container, url, count){
    let data = {};
    if(count){
      data = {
        page: {
          size: count
        }
      };
    }
    $.ajax({
      url: url,
      data: data,
      dataType: 'json',
      success: function success(results) {
        if(results.data && results.data.length){
          $container.empty();
          let $post_html = $(Drupal.theme('ln_tint_posts'));
          results.data.forEach(post => {
            $post_html.append(Drupal.theme('ln_tint_post', post));
          });
          $container.append($post_html);
          $container.find('.tint-post-modal-link').click(function(ev){
            ev.preventDefault();
            let $content = $(this).parents('.tint-post').find('.modal-data').clone();
            let $text_class = $(this).parents('.paragraph').hasClass('white-text') ? 'white-text' : 'dark-text';
            if($content.length){
              Drupal.dialog($content, {
                classes: {
                  'ui-dialog': 'tint-post-modal ' + $text_class
                },
                resizable: false,
                open: function open(event){
                  let $video = $('.tint-post-modal video');
                  if($video.length){
                    $video.trigger('play');
                  }
                },
                close: function close(event) {
                  $('.tint-post-modal').remove();
                }
              }).showModal();
            }
          });
          if(results.links){
            let current_page = $container.data('page');
            $container.append(
              Drupal.theme('ln_tint_links', results.links,
              results.data.length == count,
              current_page > 0
              )
            );
            $container.find('.ln-tint-pagination .ln-tint-link').click(function(ev){
              ev.preventDefault()
              let $this = $(this);

              if(!$container.is('.loading')){
                $container.addClass('loading');

                if($this.is('.ln-tint-prev')){
                  $container.data('page', --current_page);
                }else{
                  $container.data('page', ++current_page);
                }

                Drupal.ln_tint_connector.get_posts($container, $this.attr('href'), count);
              }
            });
          }
        }else{
          //if there are no more elements we remove the next button
          $container.find('.ln-tint-pagination .ln-tint-next').remove();
        }
      },
      complete: function complete() {
        $container.removeClass('loading');
      }
    });
  };


  Drupal.behaviors.ln_tint_connector_custom = {
    attach: function attach(context, settings) {
      $('.tint-mode-custom').once('ln-tint-load').each(function(){
        let $this = $(this);
        let tint_id = $this.data('id');

        if(tint_id){
          $this.data('page', 0);
          Drupal.ln_tint_connector.get_posts($this, settings.ln_tint_connector.api_url + tint_id + '/posts', settings.ln_tint_connector.pager_size);
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
