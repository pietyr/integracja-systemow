package org.example;

import java.util.List;

public class CityPrinter {

    public static void printCity(City city) {
        System.out.println("-------------------------");
        System.out.println("ID:          " + city.getId());
        System.out.println("Name:        " + city.getName());
        System.out.println("CountryCode: " + city.getCountryCode());
        System.out.println("District:    " + city.getDistrict());
        System.out.println("Population:  " + city.getPopulation());
    }

    public static void printAllCities(List<City> cities) {
        System.out.println("Liczba miast: " + cities.size());
        for (City city : cities) {
            printCity(city);
        }
        System.out.println("-------------------------");
    }
}