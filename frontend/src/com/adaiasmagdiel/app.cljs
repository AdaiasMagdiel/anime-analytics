(ns com.adaiasmagdiel.app
  (:require [reagent.dom.client :as rdom]
            [reagent.core :as r]
            [com.adaiasmagdiel.app.components :as c]))


(defonce app-state (r/atom {:data []}))

(defn main-component []
  [c/header])

(defonce root
  (rdom/create-root (.getElementById js/document "root")))

(defn ^:export init! []
  (rdom/render root [main-component]))

(defn ^:dev/after-load reload! []
  (init!))
