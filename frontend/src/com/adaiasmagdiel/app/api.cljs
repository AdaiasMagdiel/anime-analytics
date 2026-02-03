(ns com.adaiasmagdiel.app.api
  (:require [cljs-http.client :as http]
            [cljs.core.async :refer [go <!]]))

(def api-base
  "Automatically switches between PHP dev port and production relative paths."
  (if (and (exists? js/window)
           (= (.. js/window -location -port) "8080"))
    "http://localhost:5013" ;; Dev: Shadow-cljs talking to PHP
    ""))                    ;; Prod: Same server, relative path

