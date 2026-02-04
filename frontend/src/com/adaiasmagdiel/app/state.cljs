(ns com.adaiasmagdiel.app.state
  (:require [reagent.core :as r]))

(defonce app (r/atom {:analytics {}
                      :thinking false
                      :filters {:mode "season"
                                :year (.getFullYear (js/Date.))
                                :season (nth
                                         ["winter" "spring" "summer" "fall"]
                                         (quot (.getMonth (js/Date.)) 3))}}))