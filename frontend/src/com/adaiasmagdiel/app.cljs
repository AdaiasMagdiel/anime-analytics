(ns com.adaiasmagdiel.app
  (:require [reagent.dom.client :as rdom]
            [reagent.core :as r]))


(defonce app-state (r/atom {:data []}))

(defn main-component []
  [:main {:class ""}
   [:h1 "Hello, World!"]])

(defonce root
  (rdom/create-root (.getElementById js/document "root")))

(defn ^:export init! []
  (rdom/render root [main-component]))

(defn ^:dev/after-load reload! []
  (init!))
