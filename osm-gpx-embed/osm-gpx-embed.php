<?php
/*
  Plugin Name: OSM GPX Embed
  Description: Embed OpenStreetMap with GPX layer in WordPress
  Author: oldnapalm
  Version: 0.1
 */
add_action('admin_menu', 'osmgpx_setup_menu');
add_shortcode('osmgpx', 'osmgpx_shortcode');
add_action('wp_head', 'osmgpx_header');
add_filter('upload_mimes', 'gpx_mime_type');

function osmgpx_header() {
    $gpxjs = plugin_dir_url(__FILE__) . 'js/gpx.js';
    $gpximg = plugin_dir_url(__FILE__) . 'images/';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.1/leaflet.css" />
    <style type="text/css">
      .gpx { width: 100%; margin: 1em auto; }
      .gpx header { padding: 0.5em; }
      .gpx h3 { margin: 0; padding: 0; font-weight: bold; }
      .gpx .start { font-size: smaller; color: #444; }
      .gpx .map { border: 1px #888 solid; border-left: none; border-right: none; margin: 0; width: 100%; height: 300px; }
      .gpx footer { background: #f0f0f0; padding: 0.5em; }
      .info { text-align: center; padding: 1px; font-size: medium; color: #666; }
      .info span { color: black; }
      @media only screen and (min-width: 720px){
      .gpx .map { border: 1px #888 solid; border-left: none; border-right: none; margin: 0; width: 100%; height: 400px; }
      }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.1/leaflet.js"></script>
    <script src="' . $gpxjs . '"></script>
    <script type="application/javascript">
      function display_gpx(elt) {
        if (!elt) return;

        var url = elt.getAttribute(\'data-gpx-source\');
        var mapid = elt.getAttribute(\'data-map-target\');
        if (!url || !mapid) return;

        function _t(t) { return elt.getElementsByTagName(t)[0]; }
        function _c(c) { return elt.getElementsByClassName(c)[0]; }

        L.CRS.pr = L.extend({}, L.CRS.Simple, {
          projection: L.Projection.LonLat,
          transformation: new L.Transformation(726, 39.4, -768, 57.95),

          scale: function(zoom) {
            return Math.pow(2, zoom);
          },

          zoom: function(scale) {
            return Math.log(scale) / Math.LN2;
          },

          distance: function(latlng1, latlng2) {
            var dx = latlng2.lng - latlng1.lng,
              dy = latlng2.lat - latlng1.lat;

            return Math.sqrt(dx * dx + dy * dy);
          },

          infinite: true
        });

        var map = L.map(mapid, {
          crs: L.CRS.pr,
        }).setView([0, 0], 3);
        
        var mapheight = 11008;
        var mapwidth = 11008;
        var sw = map.unproject([0, mapheight], 7);
        var ne = map.unproject([mapwidth, 0], 7);
        var layerbounds = new L.LatLngBounds(sw, ne);
        map.setMaxBounds(layerbounds);
        var mapimage = L.tileLayer(\'https://cdn.mapgenie.io/images/tiles/gta5/los-santos/satellite/{z}/{x}/{y}.png\', {
          attribution: \'Map data &copy; <a href="https://mapgenie.io">Map Genie</a>\',
          minZoom: 3,
          maxZoom: 7,
          bounds: layerbounds,
          noWrap: true
        })
        mapimage.addTo(map);

        var tmp = new L.GPX(url, {
          async: true,
          marker_options: {
            startIconUrl: \'' . $gpximg . 'pin-icon-start.png\',
            endIconUrl:   \'' . $gpximg . 'pin-icon-end.png\',
            shadowUrl:    \'' . $gpximg . 'pin-shadow.png\',
          },
        }).on(\'loaded\', function(e) {
          if (tmp.get_color() != null) {
              tmp.setStyle({
                  color: tmp.get_color(),
                  opacity: 0.8
              });
          }
          var gpx = e.target;
          map.fitBounds(gpx.getBounds());
';
    if (get_option('osmgpx_header') != null && get_option('osmgpx_header') == 1) {
        echo '_t(\'h3\').textContent = gpx.get_name();
          _c(\'start\').textContent = gpx.get_start_time().toLocaleDateString({dateStyle: \'long\'}) + \', \' + gpx.get_start_time().toLocaleTimeString();';
    }
    $metricSystem = get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km';
    if (get_option('osmgpx_distance') != null || get_option('osmgpx_distance') == 1) {
        if($metricSystem) {
            echo '_c(\'distance\').textContent = parseFloat(gpx.m_to_km(gpx.get_distance()).toFixed(1)).toLocaleString() + \' km\';';
        } else {
            echo '_c(\'distance\').textContent = parseFloat(gpx.m_to_mi(gpx.get_distance()).toFixed(1)).toLocaleString() + \' mi\';';
        }
    }
    if (get_option('osmgpx_duration') != null && get_option('osmgpx_duration') == 1) {
        echo '_c(\'duration\').textContent = gpx.get_duration_string(gpx.get_moving_time());';
    }
    if (get_option('osmgpx_pace') != null && get_option('osmgpx_pace') == 1) {
        if($metricSystem) {
            echo '_c(\'pace\').textContent     = gpx.get_duration_string(gpx.get_moving_pace(), true) + \' / km\';';
        } else {
            echo '_c(\'pace\').textContent     = gpx.get_duration_string(gpx.get_moving_pace_imp(), true) + \' / mi\';';
        }
    }
    if (get_option('osmgpx_speed') != null && get_option('osmgpx_speed') == 1) {
        if($metricSystem) {
            echo '_c(\'speed\').textContent = parseFloat(gpx.get_moving_speed().toFixed(1)).toLocaleString() + \' km/h\';';
        } else {
            echo '_c(\'speed\').textContent = parseFloat(gpx.get_moving_speed_imp().toFixed(1)).toLocaleString() + \' mi/h\';';
        }
    }
    if (get_option('osmgpx_cadence') != null && get_option('osmgpx_cadence') == 1) {
        echo 'if(!isNaN(gpx.get_average_cadence())) { '
        . '_c(\'cadence\').textContent    = gpx.get_average_cadence() + \' rpm\'; } else {'
                . ' _c(\'cadenceDiv\').style.display = "none"; }';
    }
    if (get_option('osmgpx_avghr') != null && get_option('osmgpx_avghr') == 1) {
        echo 'if(!isNaN(gpx.get_average_hr())) { '
        . '_c(\'avghr\').textContent    = gpx.get_average_hr() + \' bpm\'; } else {'
                . ' _c(\'hrDiv\').style.display = "none"; }';
    }
    if (get_option('osmgpx_elevation') != null || get_option('osmgpx_elevation') == 1) {
        $eleFormat = get_option('osmgpx_elevationFormat') != null ? get_option('osmgpx_elevationFormat') : "1";
        if($metricSystem) {
            switch ($eleFormat) {
                case 1:
                    echo '_c(\'elevation-gain\').textContent = gpx.get_elevation_gain().toFixed(0) + \' m\';';
                    break;
                case 2:
                    echo '_c(\'elevation-gain\').textContent = \'+\' + gpx.get_elevation_gain().toFixed(0) + \' m\'; _c(\'elevation-loss\').textContent = \'-\' + gpx.get_elevation_loss().toFixed(0) + \' m\';';
                    break;
                case 3:
                    echo '_c(\'elevation-gain\').textContent = \'+\' + gpx.get_elevation_gain().toFixed(0) + \' m\'; _c(\'elevation-loss\').textContent = \'-\' + gpx.get_elevation_loss().toFixed(0) + \' m\'; _c(\'elevation-net\').textContent  = (gpx.get_elevation_gain() - gpx.get_elevation_loss()).toFixed(0) + \' m\'; ';
                    break;
            }
        } else {
            switch ($eleFormat) {
                case 1:
                    echo '_c(\'elevation-gain\').textContent = gpx.get_elevation_gain_imp().toFixed(0) + \' ft\';';
                    break;
                case 2:
                    echo '_c(\'elevation-gain\').textContent = \'+\' + gpx.get_elevation_gain_imp().toFixed(0) + \' ft\'; _c(\'elevation-loss\').textContent = \'-\' + gpx.get_elevation_loss_imp().toFixed(0) + \' ft\';';
                    break;
                case 3:
                    echo '_c(\'elevation-gain\').textContent = \'+\' + gpx.get_elevation_gain_imp().toFixed(0) + \' ft\'; _c(\'elevation-loss\').textContent = \'-\' + gpx.get_elevation_loss_imp().toFixed(0) + \' ft\'; _c(\'elevation-net\').textContent  = (gpx.get_elevation_gain_imp() - gpx.get_elevation_loss_imp()).toFixed(0) + \' ft\'; ';
                    break;
            }
        }
    }
    /*
      //_t(\'h3\').textContent = gpx.get_name();
      //_c(\'start\').textContent = gpx.get_start_time().toDateString() + \', \' + gpx.get_start_time().toLocaleTimeString();
      _c(\'distance\').textContent = gpx.m_to_km(gpx.get_distance()).toFixed(1);
      //_c(\'duration\').textContent = gpx.get_duration_string(gpx.get_moving_time());
      //_c(\'pace\').textContent     = gpx.get_duration_string(gpx.get_moving_pace(), true);
      //_c(\'avghr\').textContent    = gpx.get_average_hr();
      _c(\'elevation-gain\').textContent = gpx.get_elevation_gain().toFixed(0);
      //_c(\'elevation-loss\').textContent = gpx.get_elevation_loss().toFixed(0);
      //_c(\'elevation-net\').textContent  = (gpx.get_elevation_gain() - gpx.get_elevation_loss()).toFixed(0); */
    echo '}).addTo(map);
      }
    </script>
    ';
}

function gpx_mime_type($mime_types) {
    $mime_types['gpx'] = 'application/xml';
    #$mime_types['gpx2'] = 'text/xml';
    #$mime_types['gpx3'] = 'application/gpx';
    #$mime_types['gpx4'] = 'application/gpx+xml';
    return $mime_types;
}

function osmgpx_setup_menu() {
    add_menu_page('OSM GPX Embed', 'OSM-GPX Options', 'manage_options', 'osmgpx-plugin', 'osmgpx_init');
}

function osmgpx_shortcode($atts = [], $content = null) {
    extract(shortcode_atts(array(
        'mid' => 'mid'), $atts));
    $content = wp_get_attachment_url(esc_attr($mid));
    $embed = '<section id="mapa' . esc_attr($mid) . '" class="gpx" data-gpx-source="' . $content . '" data-map-target="inner-' . esc_attr($mid) . '" style="padding-top: 0px; padding-bottom: 0px;\">';
    if (get_option('osmgpx_header') != null && get_option('osmgpx_header') == 1) {
        $embed .= '<header>
        <h3>Loading...</h3>
        <span class="start"></span>
      </header>';
    }
    $embed .= '<article>
        <div class="map" id="inner-' . esc_attr($mid) . '"></div>
      </article>

      <footer>
        <div class="info" align="center">';
    $optionWritten = false;
    if (get_option('osmgpx_distance') != null || get_option('osmgpx_distance') == 1) {
        $embed .= '<nobr>' .(get_option('osmgpx_distLabel') == null ? 'Distance' : get_option('osmgpx_distLabel'));
        $embed .= " ";
        $embed .= '<span class="distance"></span>';
        $embed .= '</nobr>';
        //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'km' : 'mi');
        $optionWritten = true;
    }
    if (get_option('osmgpx_elevation') != null && get_option('osmgpx_elevation') == 1) {
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' .(get_option('osmgpx_elevationLabel') == null ? 'Elevation' : get_option('osmgpx_elevationLabel'));
        $embed .= " ";
        $eleFormat = get_option('osmgpx_elevationFormat') != null ? get_option('osmgpx_elevationFormat') : "1";
        switch ($eleFormat) {
            case 1: $embed .= '<span class="elevation-gain"></span>';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm' : 'ft');
                break;
            case 2: $embed .= '<span class="elevation-gain"></span>';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm' : 'ft');
                $embed .= ' <span class="elevation-loss"></span>';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm' : 'ft');
                break;
            case 3: $embed .= '<span class="elevation-gain"></span>';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm' : 'ft');
                $embed .= ' <span class="elevation-loss"></span>';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm' : 'ft');
                $embed .= ' (' .(get_option('osmgpx_elevationNetLabel') == null ? 'net: ' : get_option('osmgpx_elevationNetLabel')) . ' <span class="elevation-net"></span>)';
                //$embed .= (get_option('osmgpx_defaultUnit') == null || get_option('osmgpx_defaultUnit') == 'km' ? 'm)' : 'ft)');
                break;
        }
        $embed .= '</nobr>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_duration') != null && get_option('osmgpx_duration') == 1) {
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' . (get_option('osmgpx_durationLabel') == null ? 'Duration' : get_option('osmgpx_durationLabel'));
        $embed .= " ";
        $embed .= '<span class="duration"></span>';
        $embed .= '</nobr>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_pace') != null && get_option('osmgpx_pace') == 1) {
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' .(get_option('osmgpx_paceLabel') == null ? 'Pace' : get_option('osmgpx_paceLabel'));
        $embed .= " ";
        $embed .= '<span class="pace"></span>';
        $embed .= '</nobr>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_speed') != null && get_option('osmgpx_speed') == 1) {
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' .(get_option('osmgpx_speedLabel') == null ? 'Pace' : get_option('osmgpx_speedLabel'));
        $embed .= " ";
        $embed .= '<span class="speed"></span>';
        $embed .= '</nobr>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_cadence') != null && get_option('osmgpx_cadence') == 1) {
        $embed .= '<div class="cadenceDiv info" style="display: inline-block; margin-right: 0ch; margin-left: 0.2ch;">';
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' .(get_option('osmgpx_cadenceLabel') == null ? 'Avg cadence' : get_option('osmgpx_cadenceLabel'));
        $embed .= " ";
        $embed .= '<span class="cadence"></span>';
        $embed .= '</nobr></div>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_avghr') != null && get_option('osmgpx_avghr') == 1) {
        $embed .= '<div class="hrDiv info" style="display: inline-block; margin-right: 0ch; margin-left: 0.2ch;">';
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<nobr>' .(get_option('osmgpx_avghrLabel') == null ? 'Avg HR' : get_option('osmgpx_avghrLabel'));
        $embed .= " ";
        $embed .= '<span class="avghr"></span>';
        $embed .= '</nobr></div>';
        $optionWritten = true;
    }
    if (get_option('osmgpx_download') != null && get_option('osmgpx_download') == 1) {
        if ($optionWritten == true) {
            $embed .= ' &mdash; ';
        }
        $embed .= '<a href="' . $content . '" target="_blank"><nobr>'. (get_option('osmgpx_downloadLabel') == null ? 'Download GPX' : get_option('osmgpx_downloadLabel')) . '</nobr></a>';
        //$optionWritten = true;
    }

    $embed .= '</div>
      </footer>
    </section>

    <script type="application/javascript">
      display_gpx(document.getElementById(\'mapa' . esc_attr($mid) . '\'));
    </script>';
    return $embed;
}

function osmgpx_init() {
    osmgpx_handle_post();
    ?>
    <div class="wrap">
        <h1>OSM GPX Embed</h1>
        <h2>Upload GPX file</h2>
        <form  method="POST" enctype="multipart/form-data">
            <input type='file' id='upload_gpx' name='upload_gpx'></input>
            <?php submit_button('Upload') ?>
        </form>
    </div>
    <div class="wrap">
        <h2>Display options</h2>
        <form method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="defaultUnit">Default unit system</label></th>
                        <td><select name="defaultUnit" id="defaultUnit" type="text">
                                <option value="km" <?= get_option('osmgpx_defaultUnit') != null ? (get_option('osmgpx_defaultUnit') == "km" ? "selected" : "") : ""; ?>>Metric (km)</option>
                                <option value="mi" <?= get_option('osmgpx_defaultUnit') != null ? (get_option('osmgpx_defaultUnit') == "mi" ? "selected" : "") : ""; ?>>Imperial (mi)</option>
                            </select></td>
                    </tr>

                    <tr><td colspan="2"><input name="header" id="header" type="checkbox" value="1" <?= get_option('osmgpx_header') != null ? (get_option('osmgpx_header') == 1 ? "checked" : "") : ""; ?> /> <label for="header">Show header (title and timestamp above map)</label></td></tr>

                    <tr><td colspan="2"><hr /></td></tr>

                    <tr><td colspan="2"><input name="distance" id="distance" type="checkbox" value="1" <?= get_option('osmgpx_distance') != null ? (get_option('osmgpx_distance') == 1 ? "checked" : "") : "checked"; ?> /> <label for="distance">Show distance</label></td></tr>
                    <tr>
                        <th><label for="distLabel">Distance label</label></th>
                        <td><input name="distLabel" id="distLabel" type="text" value="<?= get_option('osmgpx_distLabel') != null ? get_option('osmgpx_distLabel') : "Distance"; ?>" class="regular-text" /></td>
                    </tr>

                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="duration" id="duration" type="checkbox" value="1" <?= get_option('osmgpx_duration') != null ? (get_option('osmgpx_duration') == 1 ? "checked" : "") : ""; ?> /> <label for="duration">Show duration</label></td></tr>
                    <tr>
                        <th><label for="durationLabel">Duration label</label></th>
                        <td><input name="durationLabel" id="durationLabel" type="text" value="<?= get_option('osmgpx_durationLabel') != null ? get_option('osmgpx_durationLabel') : "Duration"; ?>" class="regular-text" /></td>
                    </tr>

                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="pace" id="pace" type="checkbox" value="1" <?= get_option('osmgpx_pace') != null ? (get_option('osmgpx_pace') == 1 ? "checked" : "") : ""; ?> /> 
                            <label for="pace">Show pace</label></td></tr>
                    <tr>
                        <th><label for="paceLabel">Pace label</label></th>
                        <td><input name="paceLabel" id="paceLabel" type="text" value="<?= get_option('osmgpx_paceLabel') != null ? get_option('osmgpx_paceLabel') : "Pace"; ?>" class="regular-text" /></td>
                    </tr>

                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="speed" id="speed" type="checkbox" value="1" <?= get_option('osmgpx_speed') != null ? (get_option('osmgpx_speed') == 1 ? "checked" : "") : ""; ?> /> 
                            <label for="speed">Show average speed</label></td></tr>
                    <tr>
                        <th><label for="speedLabel">Average speed label</label></th>
                        <td><input name="speedLabel" id="speedLabel" type="text" value="<?= get_option('osmgpx_speedLabel') != null ? get_option('osmgpx_speedLabel') : "Speed"; ?>" class="regular-text" /></td>
                    </tr>     
                    
                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="cadence" id="cadence" type="checkbox" value="1" <?= get_option('osmgpx_cadence') != null ? (get_option('osmgpx_cadence') == 1 ? "checked" : "") : ""; ?> /> 
                            <label for="cadence">Show average cadence</label></td></tr>
                    <tr>
                        <th><label for="cadenceLabel">Avg cadence label</label></th>
                        <td><input name="cadenceLabel" id="cadenceLabel" type="text" value="<?= get_option('osmgpx_cadenceLabel') != null ? get_option('osmgpx_cadenceLabel') : "Avg cadence"; ?>" class="regular-text" /></td>
                    </tr>
                    
                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="avghr" id="avghr" type="checkbox" value="1" <?= get_option('osmgpx_avghr') != null ? (get_option('osmgpx_avghr') == 1 ? "checked" : "") : ""; ?> /> 
                            <label for="avghr">Show average heart rate</label></td></tr>
                    <tr>
                        <th><label for="avghrLabel">Avg heart rate label</label></th>
                        <td><input name="avghrLabel" id="avghrLabel" type="text" value="<?= get_option('osmgpx_avghrLabel') != null ? get_option('osmgpx_avghrLabel') : "Avg HR"; ?>" class="regular-text" /></td>
                    </tr>


                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="elevation" id="elevation" type="checkbox" value="1" <?= get_option('osmgpx_elevation') != null ? (get_option('osmgpx_elevation') == 1 ? "checked" : "") : "checked"; ?> /> <label for="elevation">Show elevation</label></td></tr>
                    <tr>
                        <th><label for="elevationLabel">Elevation label</label></th>
                        <td><input name="elevationLabel" id="elevationLabel" type="text" value="<?= get_option('osmgpx_elevationLabel') != null ? get_option('osmgpx_elevationLabel') : "Elevation"; ?>" class="regular-text" /></td>
                    </tr>
                    
                    <tr>
                        <th><label for="elevationNetLabel">Elevation net label</label></th>
                        <td><input name="elevationNetLabel" id="elevationNetLabel" type="text" value="<?= get_option('osmgpx_elevationNetLabel') != null ? get_option('osmgpx_elevationNetLabel') : "net: "; ?>" class="regular-text" /></td>
                    </tr>
                                        
                    <tr>
                        <th><label for="elevationFormat">Elevation format</label></th>
                        <td><select name="elevationFormat" id="elevationFormat" type="text">
                                <option value="1" <?= get_option('osmgpx_elevationFormat') != null ? (get_option('osmgpx_elevationFormat') == "1" ? "selected" : "") : ""; ?>>Elevation_gain</option>
                                <option value="2" <?= get_option('osmgpx_elevationFormat') != null ? (get_option('osmgpx_elevationFormat') == "2" ? "selected" : "") : ""; ?>>+Elevation_gain -Elevation_loss</option>
                                <option value="3" <?= get_option('osmgpx_elevationFormat') != null ? (get_option('osmgpx_elevationFormat') == "3" ? "selected" : "") : ""; ?>>+Elevation_gain -Elevation_gain ([+-]Elevation_net)</option>
                            </select></td>
                    </tr>
                    
                    <tr><td colspan="2"><hr /></td></tr>
                    <tr><td colspan="2"><input name="download" id="download" type="checkbox" value="1" <?= get_option('osmgpx_download') != null ? (get_option('osmgpx_download') == 1 ? "checked" : "") : "checked"; ?> /> <label for="download">Show GPX download link</label></td></tr>
                    <tr>
                        <th><label for="downloadLabel">Download link label</label></th>
                        <td><input name="downloadLabel" id="downloadLabel" type="text" value="<?= get_option('osmgpx_downloadLabel') != null ? get_option('osmgpx_downloadLabel') : "Download GPX"; ?>" class="regular-text" /></td>
                    </tr>

                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save options">
            </p>
        </form>
    </div>
    <div class="wrap">    
        <h2>List of GPXs</h2>
        <?php
        $query_images_args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'application/xml',
            'post_status' => 'inherit',
            'posts_per_page' => - 1,
        );

        $query_images = new WP_Query($query_images_args);

        $images = array();
        echo "<ul>";
        foreach ($query_images->posts as $image) {
            echo "<li><b>" . $image->ID . "</b>: " . wp_get_attachment_url($image->ID) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
        #$mimes = get_allowed_mime_types();
        #$types = array();
        #foreach ($mimes as $ext => $mime) {
        #    $types[] = '<li>' . str_replace('|', ', ', $ext) . '</li>';
        #}
        #echo '<ul>' . implode('', $types) . '</ul>';
    }

    function osmgpx_handle_post() {
        if (isset($_FILES['upload_gpx'])) {
            $gpx = $_FILES['upload_gpx'];

            $uploaded = media_handle_upload('upload_gpx', 0);
            if (is_wp_error($uploaded)) {
                echo "<p>Error uploading file: <pre>" . $uploaded->get_error_message() . "</pre></p>";
            } else {
                echo "<p>File upload successful: ";
                echo '<script type="application/javascript">'
                . 'function toCb() {'
                . ' var a = document.getElementById(\'gpxcd\');'
                . ' a.select();'
                . ' document.execCommand(\'copy\');'
                . '}'
                . '</script>';
                echo "<input type=\"text\" id=\"gpxcd\" readonly value=\"[osmgpx mid=&quot;" . $uploaded . "&quot;]\"\">"
                . "<button onclick=\"toCb()\">Copy to clipboard</button></p>";
            }
        } elseif (isset($_POST['defaultUnit'])) {
            update_option('osmgpx_defaultUnit', $_POST['defaultUnit']);
            // checkboxes
            update_option('osmgpx_header', (isset($_POST['header']) ? 1 : 0));
            update_option('osmgpx_distance', (isset($_POST['distance']) ? 1 : 0));
            update_option('osmgpx_duration', (isset($_POST['duration']) ? 1 : 0));
            update_option('osmgpx_pace', (isset($_POST['pace']) ? 1 : 0));
            update_option('osmgpx_speed', (isset($_POST['speed']) ? 1 : 0));
            update_option('osmgpx_cadence', (isset($_POST['cadence']) ? 1 : 0));
            update_option('osmgpx_avghr', (isset($_POST['avghr']) ? 1 : 0));
            update_option('osmgpx_elevation', (isset($_POST['elevation']) ? 1 : 0));
            update_option('osmgpx_download', (isset($_POST['download']) ? 1 : 0));
            // labels
            update_option('osmgpx_distLabel', sanitize_text_field($_POST['distLabel']));
            update_option('osmgpx_durationLabel', sanitize_text_field($_POST['durationLabel']));
            update_option('osmgpx_paceLabel', sanitize_text_field($_POST['paceLabel']));
            update_option('osmgpx_speedLabel', sanitize_text_field($_POST['speedLabel']));
            update_option('osmgpx_cadenceLabel', sanitize_text_field($_POST['cadenceLabel']));
            update_option('osmgpx_avghrLabel', sanitize_text_field($_POST['avghrLabel']));
            update_option('osmgpx_elevationLabel', sanitize_text_field($_POST['elevationLabel']));
            update_option('osmgpx_elevationNetLabel', sanitize_text_field($_POST['elevationNetLabel']));
            update_option('osmgpx_downloadLabel', sanitize_text_field($_POST['downloadLabel']));
            // comboboxes
            update_option('osmgpx_defaultUnit', sanitize_text_field($_POST['defaultUnit']));
            update_option('osmgpx_elevationFormat', sanitize_text_field($_POST['elevationFormat']));

            echo "<script>alert('Settings saved successtully.');</script>";
        }
    }
    ?>
