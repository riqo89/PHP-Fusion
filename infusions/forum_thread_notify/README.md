# Forenthema-Benachrichtigung
Eine einfache Infusion bzw. Modifikation nebst Benutzerfeld, die es ermöglicht, bei Erstellung eines neuen Forenthemas eine Benachrichtigung via E-Mail an die entsprechenden Nutzer zu erhalten. Der Versand wirkt sich auf jedes neue Forenthema aus und lässt sich derzeit nicht differenzieren.

Hierzu ist eine Modifikation in der Infusion "Forum" erforderlich (Einfügen einer Zeile).

### Weitere Features
- Einstellung im Profil via Benutzerfeld, ob eine Benachrichtigung verschickt werden soll (Ja / Nein)
- Individueller Benachrichtungsinhalt möglich durch neues E-Mail-Template (Aktivierung nicht vergessen), ansonsten Standard-Antwort.


### Modifikation der Infusion "Forum"
Damit Benachrichtigungen verschickt werden können, muss folgende Datei angepasst bzw. um eine Zeile ergänzt werden:
```
/infusions/forum/classes/postify/new.php
```

Ergänzt die vorhandene Funktion "execute" von
```php
public function execute() {
    add_to_title(self::$locale['global_201'].self::$locale['forum_0501']);
    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0501']]);
    render_postify([
        'title'   => self::$locale['forum_0501'],
        'message' => $this->get_postify_error_message() ?: self::$locale['forum_0543'],
        'error'   => $this->get_postify_error_message(),
        'link'    => $this->get_postify_uri()
    ]);
} 
```
zu
```php
public function execute() {
    add_to_title(self::$locale['global_201'].self::$locale['forum_0501']);
    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0501']]);
    if(defined('FORUM_THREAD_NOTIFY_EXIST')) require_once FORUM_THREAD_NOTIFY_INCLUDES."forum_thread_notify.php";
    render_postify([
        'title'   => self::$locale['forum_0501'],
        'message' => $this->get_postify_error_message() ?: self::$locale['forum_0543'],
        'error'   => $this->get_postify_error_message(),
        'link'    => $this->get_postify_uri()
    ]);
}
```

**Achtet darauf, dass sowohl die Infusion "Forum", die gegenständliche Infusion "Forenthema-Benachrichtigung" sowie das Benutzerfeld aktiviert bzw. installiert sind!**

---

# Forum thread notification
A simple infusion or modification in addition to a user field, which makes it possible to receive a notification via e-mail to the relevant user when a new forum thread is created. The notification affects every new forum thread and cannot currently be differentiated.

This requires a modification in the "Forum" infusion (insert a line).

### More features
- Setting in the profile via the user field whether a notification should be sent (yes / no)
- Individual notification content possible through a new e-mail template (don't forget to activate it), otherwise standard response.


### Modification of the infusion "Forum"
So that notifications can be sent, the following file must be adapted or a line added:
```
/infusions/forum/classes/postify/new.php
```

Complements the existing "execute" function of
```php
public function execute() {
    add_to_title(self::$locale['global_201'].self::$locale['forum_0501']);
    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0501']]);
    render_postify([
        'title'   => self::$locale['forum_0501'],
        'message' => $this->get_postify_error_message() ?: self::$locale['forum_0543'],
        'error'   => $this->get_postify_error_message(),
        'link'    => $this->get_postify_uri()
    ]);
} 
```
to
```php
public function execute() {
    add_to_title(self::$locale['global_201'].self::$locale['forum_0501']);
    BreadCrumbs::getInstance()->addBreadCrumb(['link' => FUSION_REQUEST, 'title' => self::$locale['forum_0501']]);
    if(defined('FORUM_THREAD_NOTIFY_EXIST')) require_once FORUM_THREAD_NOTIFY_INCLUDES."forum_thread_notify.php";
    render_postify([
        'title'   => self::$locale['forum_0501'],
        'message' => $this->get_postify_error_message() ?: self::$locale['forum_0543'],
        'error'   => $this->get_postify_error_message(),
        'link'    => $this->get_postify_uri()
    ]);
}
```

**Make sure that the infusion "Forum", the actual infusion "Forum topic notification" and the user field are activated / installed!**