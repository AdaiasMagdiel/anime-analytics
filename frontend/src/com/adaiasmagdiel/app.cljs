(ns com.adaiasmagdiel.app
  (:require [reagent.dom.client :as rdom]
            [com.adaiasmagdiel.app.components :as c]
            [com.adaiasmagdiel.app.components.page-header :as page-header]
            [com.adaiasmagdiel.app.components.charts :as charts]
            [com.adaiasmagdiel.app.api :as api]))

(defn main-component []
  [:<>
   [c/header]
   [:main {:class "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"}
    [page-header/root]
    [charts/root]]])

(defonce root
  (rdom/create-root (.getElementById js/document "root")))

(defn ^:export init! []
  (api/fetch-analytics)
  (rdom/render root [main-component]))

(defn ^:dev/after-load reload! []
  (init!))
