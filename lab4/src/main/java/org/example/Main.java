package org.example;

import jakarta.persistence.EntityManager;
import jakarta.persistence.EntityManagerFactory;
import jakarta.persistence.Persistence;
import jakarta.persistence.Query;

import java.util.List;

//TIP To <b>Run</b> code, press <shortcut actionId="Run"/> or
// click the <icon src="AllIcons.Actions.Execute"/> icon in the gutter.
public class Main {
    static void main() {
        System.out.println("JPA project");
        EntityManagerFactory factory =
                Persistence.createEntityManagerFactory("Hibernate_JPA");
        EntityManager em = factory.createEntityManager();
        em.getTransaction().begin();
        User u1 = new User(null, "test_1","test_1","Andrzej",
                "Kowalski", Sex.MALE);
        em.persist(u1);
        User u2 = new User(null, "test_2","test_2","Karol",
                "Nowak", Sex.MALE);
        em.persist(u2);
        User u3 = new User(null, "test_3","test_3","Agnieszka",
                "Kowalska", Sex.FEMALE);
        em.persist(u3);
        User u4 = new User(null, "test_4","test_4","Mateusz",
                "Chmielewski", Sex.MALE);
        em.persist(u4);
        User u5 = new User(null, "test_5","test_5","Katarzyna",
                "Laskowska", Sex.FEMALE);
        em.persist(u5);

        Role r1 = new Role(null, "admin");
        em.persist(r1);

        Role r2 = new Role(null, "user");
        em.persist(r2);
        Role r3 = new Role(null, "moderator");
        em.persist(r3);
        Role r4 = new Role(null, "owner");
        em.persist(r4);
        Role r5 = new Role(null, "employee");
        em.persist(r5);



//      Z.4.3.2.
        User user1 = em.find(User.class, 1);
        user1.setPassword("noweHaslo");
        em.merge(user1);

//      Z.4.3.3.
        User user5 = em.find(User.class, 5);
        em.remove(user5);

// Z.4.3.4.
        Query query = em.createQuery("SELECT u FROM User u WHERE u.lastName = 'Kowalski'");
        List<User> kowalscy = query.getResultList();
        System.out.println(kowalscy);

// Z.4.3.5.
        Query query2 = em.createQuery("SELECT u FROM User u WHERE u.sex = Sex.FEMALE");
        List<User> kobiety = query2.getResultList();
        System.out.println(kobiety);

        em.getTransaction().commit();
        em.close();
        factory.close();
    }
}
