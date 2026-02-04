(ns com.adaiasmagdiel.app.api
  (:require [cljs-http.client :as http]
            [cljs.core.async :refer [go <!]]
            [com.adaiasmagdiel.app.state :as state]))

(def api-base
  "Automatically switches between PHP dev port and production relative paths."
  (if (and (exists? js/window)
           (= (.. js/window -location -port) "8080"))
    "http://localhost:5013" ;; Dev: Shadow-cljs talking to PHP
    ""))                    ;; Prod: Same server, relative path

(defn fetch-api
	([url] (fetch-api url {}))
	([url params]
		(http/get (str api-base url) params)))

(defn fetch-analytics []
  (let [{:keys [mode year season]} (:filters @state/app)]
    (swap! state/app assoc :thinking true)
    
    (go
      (cond
        (= mode "year")
        (let [response (<! (fetch-api (str "/api/year/" year "/analytics")))
              data     (get-in response [:body :data])]
          (swap! state/app assoc :analytics data))

        (and (= mode "season") (not= "" season))
        (let [response (<! (fetch-api
        																				(str "/api/season/analytics")
        																				{:query-params {"season" season
        																																				"year" year}}))
              data     (get-in response [:body :data])]
          (swap! state/app assoc :analytics data)))
      
      (swap! state/app assoc :thinking false))))