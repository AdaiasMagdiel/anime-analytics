(ns com.adaiasmagdiel.app.components.charts
  (:require [com.adaiasmagdiel.app.state :as state]
            ["lucide-react" :refer [PieChartIcon]]
            ["recharts" :refer [PieChart Pie ResponsiveContainer Legend]]))

(def chart-colors ["#6366f1" "#10b981" "#f59e0b" "#8b5cf6" "#f43f5e"])

(defn donut-skeleton []
  [:div {:class "flex flex-col items-center animate-pulse w-full"}
   [:div {:class "w-48 h-48 rounded-full border-[16px] border-slate-800 mb-6"}]
   [:div {:class "space-y-3 w-full"}
    [:div {:class "h-3 bg-slate-800 rounded w-full"}]
    [:div {:class "h-3 bg-slate-800 rounded w-4/5"}]]])

(defn media-types []
  (fn []
    [:section {:class "bg-[var(--color-card)] p-6 rounded-3xl border border-[var(--color-border)] flex flex-col items-center"}
     [:h3 {:class "text-sm font-bold mb-8 w-full flex items-center gap-2 uppercase tracking-wider text-indigo-300"}
      [:> PieChartIcon {:class "w-4 h-4"}]
      "Media Types"]

     (let [{:keys [thinking analytics]} @state/app
           types (map-indexed (fn [idx [k v]]
                                {:name (name k)
                                 :value v
                                 :fill (nth chart-colors (mod idx (count chart-colors)))})
                              (:types analytics))]
       (println types)
       (if thinking
         [donut-skeleton]
         [:div {:class "h-64 w-full"}
          [:> ResponsiveContainer {:width "100%" :height "100%"}
           [:> PieChart
            [:> Pie {:data types
                     :innerRadius 60
                     :outerRadius 80
                     :paddingAngle 5
                     :dataKey "value"
                     :nameKey "name"
                     :stroke "none"
                     :label true}
             [:> Legend]
             [:text {:x "50%"
                     :y "45%"
                     :textAnchor "middle"
                     :dominantBaseline "middle"
                     :class "fill-white text-2xl font-bold"}
              (:count analytics)]
             [:text {:x "50%"
                     :y "52%"
                     :textAnchor "middle"
                     :dominantBaseline "middle"
                     :class "text-[10px] uppercase fill-slate-500 font-bold"}
              "Total titles"]]]]]))]))

(defn root []
  [:div {:class "grid grid-cols-1 lg:grid-cols-12"}
   [:div {:class "lg:col-span-4 space-y-6"}
    [media-types]]])
