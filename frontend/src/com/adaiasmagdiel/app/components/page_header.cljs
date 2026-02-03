(ns com.adaiasmagdiel.app.components.page-header
  (:require [com.adaiasmagdiel.app.state :as state]))

(defn set-mode [mode]
  (swap! state/app assoc-in [:filters :mode] mode))

(defn mode-selector []
  (let [mode (:mode (:filters @state/app))]
    [:div {:class "flex bg-black rounded-xl p-2 gap-1"}
     [:button {:class ["cursor-pointer px-4 py-2 rounded-lg  text-sm font-medium shadow-sm"
                       (if (= "year" mode) "text-white bg-indigo-600" "text-slate-400 hover:text-white transition-all")]
               :on-click #(set-mode "year")}
      "Yearly"]
     [:button {:class ["cursor-pointer px-4 py-2 rounded-lg  text-sm font-medium shadow-sm"
                       (if (= "season" mode) "text-white bg-indigo-600" "text-slate-400 hover:text-white transition-all")]
               :on-click #(set-mode "season")}
      "Season"]]))

(defn root []
  [:header {:class "flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8"}
   [:div {:class "flex flex-col gap-1"}
    [:div {:class "flex items-center gap-2"}
     [:h1 {:class "font-title text-3xl font-bold text-white tracking-tight"}
      "Insights Overview"]]
    [:p {:class "text-slate-400 text-base leading-relaxed"}
     "Unveiling seasonal patterns and "
     [:span {:class "text-slate-200 font-medium"}
      "industry benchmarks"]]]

   [:div {:class "bg-card p-2 rounded-2xl border border-slate-800 flex flex-wrap items-center gap-2 shadow-xl"}
    [mode-selector]]])
