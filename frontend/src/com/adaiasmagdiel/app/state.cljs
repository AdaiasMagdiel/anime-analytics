(ns com.adaiasmagdiel.app.state
  (:require [reagent.core :as r]))

(defonce year (.getFullYear (js/Date.)))
(defonce seasons ["winter" "spring" "summer" "fall"])
(defonce years (range year 1921 -1))
(defonce month (.getMonth (js/Date.)))

(defonce app (r/atom {:analytics {}
                      :thinking false
                      :filters {:mode "season"
                                :year year
                                :season (nth seasons (quot month 3))}}))