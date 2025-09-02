<?php
class Linkding extends Plugin {
	private $host;

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);

		$host->add_hook($host::HOOK_HOTKEY_MAP, $this);
		$host->add_hook($host::HOOK_HOTKEY_INFO, $this);

	}

	function about() {
		return array(0.1,
				"Add articles to Linkding with a single click",
				"@black-roland, based on oneclickpocket by @fxneumann");
	}

	function save() {
		$linkding_url = clean($_POST["linkding_url"]);
		$linkding_api_token = clean($_POST["linkding_api_token"]);

		$this->host->set($this, "linkding_url", $linkding_url);
		$this->host->set($this, "linkding_api_token", $linkding_api_token);

		echo "Linkding URL set to: <em>$linkding_url</em><br />REST API Token set";
	}

	function api_version() {
		return 2;
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/linkding.js");
	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" class=\"material-icons\" id=\"ld$article_id\" style=\"cursor : pointer\" onclick=\"Plugins.Linkding.shareArticleToLinkding($article_id, this)\" title='".__('Add to Linkding')."'><path fill=\"none\" stroke=\"currentColor\" stroke-linejoin=\"round\" stroke-miterlimit=\"1.5\" stroke-width=\".977\" d=\"M7.785 3.457 3.371 7.871s-1.516 1.48.133 3.176c1.66 1.703 3.18.133 3.18.133l4.41-4.41\"/><path fill=\"none\" stroke=\"currentColor\" stroke-linejoin=\"round\" stroke-miterlimit=\"1.5\" stroke-width=\".977\" d=\"m8.246 12.516 4.398-4.43s1.512-1.488-.144-3.176c-1.664-1.695-3.18-.12-3.18-.12L4.926 9.214\"/></svg>";

		return $rv;
	}

	function getInfo() {
		//retrieve Data from the DB
		$id = $_REQUEST['id'];

		$sth = $this->pdo->prepare("SELECT title, link, content
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = ? AND ref_id = id AND owner_uid = ?");
		$sth->execute([$id, $_SESSION['uid']]);

		if($sth->rowCount() != 0) {
			$row = $sth->fetch();

			$article_link = $row['link'];
			$title = strip_tags($row['title']);
			$content = strip_tags($row['content']);
		}

		$linkding_url = $this->host->get($this, "linkding_url");
		$linkding_api_token = $this->host->get($this, "linkding_api_token");

		//Call Linkding API
		if (function_exists('curl_init')) {
			// First check if URL is already bookmarked
			$checkUrl = $linkding_url . '/api/bookmarks/check/?url=' . urlencode($article_link);

			$cURL = curl_init();
			curl_setopt($cURL, CURLOPT_URL, $checkUrl);
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
				'Authorization: Token ' . $linkding_api_token,
				'Content-Type: application/json'
			));
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURL, CURLOPT_TIMEOUT, 10);
			curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);

			$checkResult = curl_exec($cURL);
			$checkStatus = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
			curl_close($cURL);

			$checkData = json_decode($checkResult, true);

			// If bookmark already exists, return success
			if ($checkStatus == 200 && isset($checkData['bookmark']) && $checkData['bookmark'] !== null) {
				$status = "200";
				$message = "Already bookmarked";
			} else {
				// Create new bookmark
				$postfields = array(
					'url' => $article_link,
					'title' => $title,
					'description' => $content,
				);

				$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $linkding_url . '/api/bookmarks/');
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
					'Authorization: Token ' . $linkding_api_token,
					'Content-Type: application/json',
				));
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_TIMEOUT, 10);
				curl_setopt($cURL, CURLOPT_POST, true);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, json_encode($postfields));

				$apicall = curl_exec($cURL);
				$status = curl_getinfo($cURL, CURLINFO_HTTP_CODE);
				curl_close($cURL);

				$message = ($status == 200 || $status == 201) ? "Bookmark created" : "Error: " . $status;
			}
		} else {
			$status = '501';
			$message = 'For the plugin to work you need to <strong>enable PHP extension CURL</strong>!';
		}

		//Return information on article and status
		print json_encode(array(
			"title" => $title,
			"link" => $article_link,
			"id" => $id,
			"status" => $status,
			"message" => $message
		));
	}

	function hook_prefs_tab($args) {
		//Add preferences pane
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Linkding")."\">";

		print "<br/>";

		print "<h2>Linkding API Token Setup</h2>";
		print "<p>To use this plugin, you need to obtain an API token from your Linkding instance:</p>";
		print "<ol>";
		print "<li>Log into your Linkding instance.</li>";
		print "<li>Go to <strong>Settings → Integrations</strong>.</li>";
		print "<li>Find the \"REST API\" section.</li>";
		print "<li>Copy the API token shown there.</li>";
		print "<li>Paste it into the \"Linkding REST API Token\" field below.</li>";
		print "</ol>";
		print "<p>You also need to provide the URL of your Linkding instance in the \"Linkding URL\" field below.</p>";

		$linkding_url = $this->host->get($this, "linkding_url");
		$linkding_api_token = $this->host->get($this, "linkding_api_token");

		print "<form dojoType=\"dijit.form.Form\">";

		print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
			evt.preventDefault();
			if (this.validate()) {
				console.log(dojo.objectToQuery(this.getValues()));
				xhr.post('backend.php',
					this.getValues(),
					(reply) => { Notify.info(reply); });
			}
		</script>";

		print \Controls\pluginhandler_tags($this, "save");
		print "<table width=\"100%\" class=\"prefPrefsList\">";

		if (!function_exists('curl_init')) {
				print '<tr><td colspan="3" style="color:red;font-size:large">For the plugin to work you need to <strong>enable PHP extension CURL</strong>!</td></tr>';
		}

		print "<tr><td width=\"20%\">".__("Linkding URL")."</td>";
		print '<td width=\"20%\">Enter your Linkding instance URL (e.g., https://linkding.example.com):</td>';
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"linkding_url\" regExp='^(http|https)://.*' value=\"$linkding_url\"></td>";
		print "<tr><td width=\"20%\">".__("Linkding REST API Token")."</td>";
		print "<td width=\"20%\">Find your API token in Linkding under <strong>Settings → Integrations</strong>.</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"linkding_api_token\" value=\"$linkding_api_token\"></td></tr>";
		print "</table>";
		print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

		print "</form>";

		print "</div>"; #pane
	}

	function hook_hotkey_map($hotkeys) {
		$hotkeys['l'] = 'linkding_save';
		return $hotkeys;
	}

	function hook_hotkey_info($hotkeys) {
		$offset = 1 + array_search('open_in_new_window', array_keys($hotkeys[__('Article')]));
		$hotkeys[__('Article')] =
				array_slice($hotkeys[__('Article')], 0, $offset, true) +
				array('linkding_save' => __('Add to Linkding')) +
				array_slice($hotkeys[__('Article')], $offset, NULL, true);

		return $hotkeys;
	}

}

?>
